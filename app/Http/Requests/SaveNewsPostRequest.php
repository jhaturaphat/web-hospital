<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveNewsPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-news') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:50000'],
            'news_category_id' => ['required', 'integer', 'exists:news_categories,id'],
            'is_published' => ['required', 'boolean'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'extensions:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'กรุณาระบุ :attribute',
            'max' => ':attribute มีขนาดหรือความยาวเกินที่กำหนด',
            'attachments.max' => 'แนบไฟล์ได้ครั้งละไม่เกิน 3 ไฟล์',
            'attachments.*.max' => 'แต่ละไฟล์ต้องมีขนาดไม่เกิน 2 MB',
            'attachments.*.mimes' => 'รองรับไฟล์ PDF, Word, Excel, JPG และ PNG เท่านั้น',
            'attachments.*.extensions' => 'นามสกุลไฟล์ไม่อยู่ในประเภทที่รองรับ',
        ];
    }

    public function attributes(): array
    {
        return ['title' => 'หัวข้อข่าว', 'body' => 'รายละเอียด', 'news_category_id' => 'หมวดหมู่', 'attachments' => 'ไฟล์แนบ'];
    }
}
