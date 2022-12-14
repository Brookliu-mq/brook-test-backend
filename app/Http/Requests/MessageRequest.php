<?php

namespace App\Http\Requests;

use App\Models\School;
use App\Models\SchoolTeacher;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class MessageRequest extends ApiBaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        switch ($this->route()->getActionMethod()) {
            case 'chatMessages':
                $rule = $this->getMessageRule();
                return [
                    'student_id' => $rule
                ];
            case 'sendStudentChatMessage':
                $rule = $this->getMessageRule();
                return [
                    'student_id' => $rule,
                    'message' => ['required'],
                ];
            case 'sendTeacherMessage':
                return [
                    'message' => ['required'],
                ];
        }

    }

    public function messages()
    {
        return [
            'student_id.required' => '请选择学生',
            'message.required' => '请输入信息',
        ];
    }

    protected function getMessageRule(): array
    {
        return ['required', 'exists:students,id', function ($attribute, $value, $fail) {
            $school_id = Student::query()->where('id', $this->get('student_id'))->value('school_id');
            if (! SchoolTeacher::query()
                ->where('school_id', $school_id)
                ->where('teacher_id', Auth::id())
                ->where('is_admin', true)->exists()) {
                return $fail('无权限发送信息');
            }
        }];
    }
}
