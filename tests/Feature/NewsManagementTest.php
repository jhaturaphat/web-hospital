<?php

namespace Tests\Feature;

use App\Models\NewsAttachment;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class NewsManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function staff(): User
    {
        return User::factory()->create(['is_staff' => true]);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'news_category_id' => NewsCategory::factory()->create()->id,
            'title' => 'ประกาศทดสอบการรับสมัครงาน',
            'body' => 'รายละเอียดสำหรับผู้สนใจสมัครงาน',
            'is_published' => '0',
        ], $overrides);
    }

    public function test_guests_are_redirected_before_uploading(): void
    {
        $this->post(route('staff.posts.store'), [])->assertRedirect(route('login'));
        $this->assertDatabaseCount('news_posts', 0);
    }

    public function test_regular_users_cannot_manage_news(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('staff.posts.store'), [])->assertForbidden();
        $this->assertDatabaseCount('news_posts', 0);
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_gate_requires_staff_role(bool $isStaff): void
    {
        $user = User::factory()->create(['is_staff' => $isStaff]);

        $this->assertSame($isStaff, Gate::forUser($user)->allows('manage-news'));
    }

    public function test_staff_can_login(): void
    {
        $user = $this->staff();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('staff.posts.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_regular_account_cannot_login_as_staff(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_login_rejects_wrong_password(): void
    {
        $user = $this->staff();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password'])->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_staff_can_logout(): void
    {
        $this->actingAs($this->staff())->post(route('staff.logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_staff_can_store_a_draft_and_private_attachment(): void
    {
        Storage::fake('local');
        $data = $this->payload(['attachments' => [UploadedFile::fake()->create('ประกาศ.pdf', 100, 'application/pdf')]]);

        $this->actingAs($this->staff())->post(route('staff.posts.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('news_posts', ['title' => $data['title'], 'is_published' => false]);
        $attachment = NewsAttachment::sole();
        Storage::disk('local')->assertExists($attachment->path);
        $this->assertSame('ประกาศ.pdf', $attachment->name);
    }

    public function test_required_fields_report_errors_without_creating_a_post(): void
    {
        $this->actingAs($this->staff())->post(route('staff.posts.store'), [])->assertSessionHasErrors([
            'title' => 'กรุณาระบุ หัวข้อข่าว',
            'body' => 'กรุณาระบุ รายละเอียด',
            'news_category_id' => 'กรุณาระบุ หมวดหมู่',
        ]);
        $this->assertDatabaseCount('news_posts', 0);
    }

    #[TestWith(['script.php', 'application/x-httpd-php', 1])]
    #[TestWith(['too-large.pdf', 'application/pdf', 2049])]
    #[TestWith(['disguised.php', 'application/pdf', 1])]
    public function test_invalid_attachments_are_rejected(string $name, string $mime, int $size): void
    {
        Storage::fake('local');
        $data = $this->payload(['attachments' => [UploadedFile::fake()->create($name, $size, $mime)]]);

        $this->actingAs($this->staff())->post(route('staff.posts.store'), $data)->assertSessionHasErrors('attachments.0');
        $this->assertDatabaseCount('news_posts', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_too_many_attachments_are_rejected(): void
    {
        $files = [];
        for ($index = 0; $index < 4; $index++) {
            $files[] = UploadedFile::fake()->create('file'.$index.'.pdf', 1, 'application/pdf');
        }

        $this->actingAs($this->staff())->post(route('staff.posts.store'), $this->payload(['attachments' => $files]))->assertSessionHasErrors(['attachments' => 'แนบไฟล์ได้ครั้งละไม่เกิน 3 ไฟล์']);
        $this->assertDatabaseCount('news_posts', 0);
    }

    public function test_homepage_shows_published_news_only(): void
    {
        NewsPost::factory()->create(['title' => 'ข่าวที่เผยแพร่แล้ว', 'is_published' => true]);
        NewsPost::factory()->create(['title' => 'ข่าวฉบับร่างลับ']);

        $this->get(route('home'))->assertOk()->assertSee('ข่าวที่เผยแพร่แล้ว')->assertDontSee('ข่าวฉบับร่างลับ');
    }

    public function test_category_and_search_filters_apply_together(): void
    {
        $category = NewsCategory::factory()->create();
        NewsPost::factory()->create(['title' => 'ประกาศรับสมัครแพทย์', 'news_category_id' => $category->id, 'is_published' => true]);
        NewsPost::factory()->create(['title' => 'ข่าวจัดซื้อยา', 'news_category_id' => $category->id, 'is_published' => true]);
        NewsPost::factory()->create(['title' => 'รับสมัครพนักงานอีกหมวด', 'is_published' => true]);

        $this->get(route('news.index', ['category' => $category->id, 'q' => 'รับสมัคร']))->assertOk()->assertSee('ประกาศรับสมัครแพทย์')->assertDontSee('ข่าวจัดซื้อยา')->assertDontSee('รับสมัครพนักงานอีกหมวด');
    }

    public function test_draft_detail_is_hidden_from_guests(): void
    {
        $post = NewsPost::factory()->create();

        $this->get(route('news.show', $post))->assertNotFound();
    }

    public function test_draft_attachment_is_hidden_from_guests(): void
    {
        $post = NewsPost::factory()->create();
        $attachment = $post->attachments()->create(['path' => 'news/private.pdf', 'name' => 'private.pdf', 'size' => 100]);

        $this->get(route('news.download', $attachment))->assertNotFound();
    }

    public function test_published_attachment_can_be_downloaded(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('news/public.pdf', '%PDF-1.4 test');
        $post = NewsPost::factory()->create(['is_published' => true]);
        $attachment = $post->attachments()->create(['path' => 'news/public.pdf', 'name' => 'public.pdf', 'size' => 13]);

        $this->get(route('news.download', $attachment))->assertOk()->assertDownload('public.pdf')->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_staff_can_read_draft_and_all_management_views(): void
    {
        $post = NewsPost::factory()->create();
        $this->actingAs($this->staff());

        $this->get(route('news.show', $post))->assertOk()->assertSee('ฉบับร่าง');
        $this->get(route('staff.posts.index'))->assertOk();
        $this->get(route('staff.posts.create'))->assertOk();
        $this->get(route('staff.posts.edit', $post))->assertOk();
    }

    public function test_untrusted_news_and_category_content_is_escaped(): void
    {
        $dangerous = '<script>alert(1)</script>';
        $category = NewsCategory::factory()->create(['name' => $dangerous]);
        $post = NewsPost::factory()->create(['news_category_id' => $category->id, 'title' => $dangerous, 'body' => $dangerous, 'is_published' => true]);

        $this->get(route('news.show', $post))->assertOk()->assertSee($dangerous)->assertDontSee($dangerous, false);
    }

    public function test_staff_can_publish_and_unpublish_existing_news(): void
    {
        $post = NewsPost::factory()->create();
        $data = $this->payload(['is_published' => '1']);
        $this->actingAs($this->staff());

        $this->put(route('staff.posts.update', $post), $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('news_posts', ['id' => $post->id, 'is_published' => true]);
        $this->put(route('staff.posts.update', $post), [...$data, 'is_published' => '0'])->assertRedirect();
        $this->assertDatabaseHas('news_posts', ['id' => $post->id, 'is_published' => false]);
    }

    public function test_deleting_news_removes_its_attachment_files(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('news/delete.pdf', 'test');
        $post = NewsPost::factory()->create();
        $post->attachments()->create(['path' => 'news/delete.pdf', 'name' => 'delete.pdf', 'size' => 4]);

        $this->actingAs($this->staff())->delete(route('staff.posts.destroy', $post))->assertRedirect(route('staff.posts.index'));
        $this->assertModelMissing($post);
        $this->assertDatabaseCount('news_attachments', 0);
        Storage::disk('local')->assertMissing('news/delete.pdf');
    }

    public function test_attachment_cannot_be_deleted_through_another_post(): void
    {
        $post = NewsPost::factory()->create();
        $other = NewsPost::factory()->create();
        $attachment = $other->attachments()->create(['path' => 'news/other.pdf', 'name' => 'other.pdf', 'size' => 4]);

        $this->actingAs($this->staff())->delete(route('staff.attachments.destroy', [$post, $attachment]))->assertNotFound();
        $this->assertModelExists($attachment);
    }

    public function test_staff_can_delete_one_attachment_without_deleting_news(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('news/one.pdf', 'test');
        $post = NewsPost::factory()->create();
        $attachment = $post->attachments()->create(['path' => 'news/one.pdf', 'name' => 'one.pdf', 'size' => 4]);

        $this->actingAs($this->staff())->delete(route('staff.attachments.destroy', [$post, $attachment]))->assertRedirect();
        $this->assertModelMissing($attachment);
        $this->assertModelExists($post);
        Storage::disk('local')->assertMissing('news/one.pdf');
    }

    public function test_staff_can_add_rename_and_delete_empty_categories(): void
    {
        $this->actingAs($this->staff());

        $this->post(route('staff.categories.store'), ['name' => 'ข่าวชุมชน'])->assertRedirect();
        $category = NewsCategory::where('name', 'ข่าวชุมชน')->sole();
        $this->put(route('staff.categories.update', $category), ['name' => 'กิจกรรมชุมชน'])->assertRedirect();
        $this->assertDatabaseHas('news_categories', ['id' => $category->id, 'name' => 'กิจกรรมชุมชน']);
        $this->delete(route('staff.categories.destroy', $category))->assertRedirect();
        $this->assertModelMissing($category);
    }

    public function test_category_in_use_cannot_be_deleted(): void
    {
        $post = NewsPost::factory()->create();

        $this->actingAs($this->staff())->delete(route('staff.categories.destroy', $post->category))->assertSessionHasErrors('category');
        $this->assertModelExists($post->category);
        $this->assertModelExists($post);
    }

    public function test_create_staff_command_saves_a_staff_account(): void
    {
        $this->artisan('hospital:create-staff')->expectsQuestion('ชื่อเจ้าหน้าที่', 'เจ้าหน้าที่ข่าว')->expectsQuestion('อีเมล', 'editor@example.test')->expectsQuestion('รหัสผ่าน (อย่างน้อย 12 ตัวอักษร)', 'test-only-password')->expectsQuestion('ยืนยันรหัสผ่าน', 'test-only-password')->assertSuccessful();
        $this->assertDatabaseHas('users', ['email' => 'editor@example.test', 'is_staff' => true]);
    }
}
