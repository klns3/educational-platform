<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\SupportTicket;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AiAnalyticsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Администратор',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'class_group_id' => null,
            ]
        );

        $teacher = User::updateOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Преподаватель ИИ',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'class_group_id' => null,
            ]
        );

        $groupA = ClassGroup::updateOrCreate(
            ['name' => 'ИИ-101'],
            ['description' => 'Демонстрационная группа для анализа учебной активности.']
        );

        $groupB = ClassGroup::updateOrCreate(
            ['name' => 'ИИ-102'],
            ['description' => 'Демонстрационная группа со смешанным уровнем подготовки.']
        );

        $course = Course::updateOrCreate(
            ['title' => 'Основы машинного обучения'],
            [
                'description' => 'Курс с тестами и попытками для демонстрации ИИ-аналитики.',
                'teacher_id' => $teacher->id,
            ]
        );

        $tests = collect([
            ['title' => 'Введение в анализ данных', 'max_score' => 10],
            ['title' => 'Классификация и метрики', 'max_score' => 10],
            ['title' => 'Кластеризация K-Means', 'max_score' => 10],
            ['title' => 'Логистическая регрессия', 'max_score' => 10],
            ['title' => 'Экспертные системы', 'max_score' => 10],
            ['title' => 'Итоговый тест по ML', 'max_score' => 10],
        ])->map(fn (array $test) => Test::updateOrCreate(
            [
                'course_id' => $course->id,
                'title' => $test['title'],
            ],
            [
                'author_id' => $teacher->id,
                'description' => 'Демонстрационный тест для ИИ-аналитики.',
                'time_limit' => 45,
                'attempts_limit' => 3,
                'is_published' => true,
                'is_archived' => false,
            ]
        ));

        $profiles = [
            ['name' => 'Анна Смирнова', 'email' => 'student01@example.com', 'group' => $groupA, 'scores' => [10, 9, 10, 9, 10, 9], 'days' => 1, 'tickets' => 0],
            ['name' => 'Илья Кузнецов', 'email' => 'student02@example.com', 'group' => $groupA, 'scores' => [8, 8, 9, 8, 9, 8], 'days' => 2, 'tickets' => 0],
            ['name' => 'Мария Орлова', 'email' => 'student03@example.com', 'group' => $groupA, 'scores' => [7, 8, 8, 7, 8, 8], 'days' => 4, 'tickets' => 1],
            ['name' => 'Павел Морозов', 'email' => 'student04@example.com', 'group' => $groupA, 'scores' => [9, 9, 8, 8], 'days' => 9, 'tickets' => 0],
            ['name' => 'Софья Волкова', 'email' => 'student05@example.com', 'group' => $groupA, 'scores' => [6, 7, 6, 7, 6], 'days' => 8, 'tickets' => 1],
            ['name' => 'Даниил Соколов', 'email' => 'student06@example.com', 'group' => $groupA, 'scores' => [7, 6, 6, 5], 'days' => 12, 'tickets' => 2],
            ['name' => 'Кира Лебедева', 'email' => 'student07@example.com', 'group' => $groupB, 'scores' => [5, 6, 5, 6], 'days' => 15, 'tickets' => 1],
            ['name' => 'Артём Новиков', 'email' => 'student08@example.com', 'group' => $groupB, 'scores' => [8, 7, 7], 'days' => 18, 'tickets' => 0],
            ['name' => 'Ева Павлова', 'email' => 'student09@example.com', 'group' => $groupB, 'scores' => [5, 4, 5], 'days' => 22, 'tickets' => 2],
            ['name' => 'Никита Фёдоров', 'email' => 'student10@example.com', 'group' => $groupB, 'scores' => [4, 4, 3], 'days' => 28, 'tickets' => 3],
            ['name' => 'Полина Белова', 'email' => 'student11@example.com', 'group' => $groupB, 'scores' => [6, 5], 'days' => 20, 'tickets' => 4],
            ['name' => 'Марк Васильев', 'email' => 'student12@example.com', 'group' => $groupB, 'scores' => [], 'days' => 45, 'tickets' => 1],
        ];

        foreach ($profiles as $profile) {
            $student = User::updateOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['name'],
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'class_group_id' => $profile['group']->id,
                ]
            );

            $course->students()->syncWithoutDetaching([$student->id]);
            TestAttempt::where('user_id', $student->id)->delete();
            SupportTicket::where('user_id', $student->id)->delete();

            foreach ($profile['scores'] as $index => $score) {
                $createdAt = Carbon::now()->subDays((int) $profile['days'] + (count($profile['scores']) - $index));
                $test = $tests[$index % $tests->count()];

                TestAttempt::unguarded(fn () => TestAttempt::create([
                    'test_id' => $test->id,
                    'user_id' => $student->id,
                    'score' => $score,
                    'max_score' => 10,
                    'started_at' => $createdAt->copy()->subMinutes(40),
                    'finished_at' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]));
            }

            for ($ticketIndex = 1; $ticketIndex <= $profile['tickets']; $ticketIndex++) {
                SupportTicket::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'assigned_teacher_id' => $teacher->id,
                    'type' => SupportTicket::TYPE_COURSE_QUESTION,
                    'subject' => "Вопрос по материалу {$ticketIndex}",
                    'message' => 'Демонстрационное обращение для анализа учебных затруднений.',
                    'status' => $ticketIndex % 2 === 0 ? SupportTicket::STATUS_CLOSED : SupportTicket::STATUS_IN_PROGRESS,
                ]);
            }
        }

        $this->command?->info('AI Analytics demo users: admin@example.com / teacher@example.com / student01@example.com, password: password');
    }
}
