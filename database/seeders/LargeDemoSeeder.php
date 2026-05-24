<?php

namespace Database\Seeders;

use App\Models\ActionLog;
use App\Models\Answer;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\InvitationCode;
use App\Models\Material;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Question;
use App\Models\ScheduleEvent;
use App\Models\StudentAnswer;
use App\Models\SupportTicket;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LargeDemoSeeder extends Seeder
{
    private const PASSWORD = 'password';
    private const STUDENTS_COUNT = 160;

    public function run(): void
    {
        DB::transaction(function () {
            $this->clearDemoData();

            $admin = $this->createUser('demo.admin@example.com', 'Демо Администратор', 'admin');
            $teachers = $this->createTeachers();
            $groups = $this->createGroups();
            $courses = $this->createCourses($teachers);

            $this->createMaterials($courses, $teachers);
            $tests = $this->createTestsWithQuestions($courses, $teachers);
            $students = $this->createStudents($groups, $courses);

            $this->createAttempts($students, $tests);
            $this->createSupportTickets($students, $courses, $teachers);
            $this->createMessages($students, $teachers);
            $this->createNotifications($students, $teachers);
            $this->createSchedule($groups, $courses, $teachers);
            $this->createInvitationCodes($admin, $groups);
            $this->createActionLogs($admin, $teachers, $students);
        });

        $this->command?->info('Большая демонстрационная база создана.');
        $this->command?->line('Администратор: demo.admin@example.com / password');
        $this->command?->line('Преподаватель: demo.teacher01@example.com / password');
        $this->command?->line('Студент: demo.student001@example.com / password');
    }

    private function clearDemoData(): void
    {
        $demoUserIds = User::where('email', 'like', 'demo.%@example.com')->pluck('id');
        $demoCourseIds = Course::where('title', 'like', '[DEMO]%')
            ->orWhere('title', 'like', '[ДЕМО]%')
            ->pluck('id');
        $demoGroupIds = ClassGroup::where('name', 'like', 'DEMO-%')
            ->orWhere('name', 'like', 'ДЕМО-%')
            ->pluck('id');

        $attemptIds = TestAttempt::whereIn('user_id', $demoUserIds)
            ->orWhereIn('test_id', Test::whereIn('course_id', $demoCourseIds)->select('id'))
            ->pluck('id');

        StudentAnswer::whereIn('attempt_id', $attemptIds)->delete();
        TestAttempt::whereIn('id', $attemptIds)->delete();

        Message::whereIn('sender_id', $demoUserIds)->orWhereIn('recipient_id', $demoUserIds)->delete();
        Notification::whereIn('user_id', $demoUserIds)->orWhereIn('related_user_id', $demoUserIds)->delete();
        SupportTicket::whereIn('user_id', $demoUserIds)->orWhereIn('assigned_teacher_id', $demoUserIds)->delete();
        ScheduleEvent::whereIn('teacher_id', $demoUserIds)->orWhereIn('class_group_id', $demoGroupIds)->delete();
        InvitationCode::where('code', 'like', 'DEMO-%')->delete();
        ActionLog::whereIn('user_id', $demoUserIds)->orWhere('action', 'like', 'demo.%')->delete();

        DB::table('course_user')->whereIn('user_id', $demoUserIds)->orWhereIn('course_id', $demoCourseIds)->delete();

        $questionIds = Question::whereIn('test_id', Test::whereIn('course_id', $demoCourseIds)->select('id'))->pluck('id');
        Answer::whereIn('question_id', $questionIds)->delete();
        Question::whereIn('id', $questionIds)->delete();
        Test::whereIn('course_id', $demoCourseIds)->delete();
        Material::whereIn('course_id', $demoCourseIds)->delete();
        Course::whereIn('id', $demoCourseIds)->delete();

        User::whereIn('id', $demoUserIds)->delete();
        ClassGroup::whereIn('id', $demoGroupIds)->delete();
    }

    private function createTeachers(): array
    {
        $names = [
            'Елена Морозова',
            'Сергей Иванов',
            'Анна Петрова',
            'Дмитрий Соколов',
        ];

        return collect($names)
            ->map(fn (string $name, int $index) => $this->createUser(
                sprintf('demo.teacher%02d@example.com', $index + 1),
                $name,
                'teacher'
            ))
            ->all();
    }

    private function createGroups(): array
    {
        return collect(range(1, 8))
            ->map(fn (int $index) => ClassGroup::create([
                'name' => sprintf('ДЕМО-%03d', 100 + $index),
                'description' => 'Демонстрационная учебная группа для полной проверки платформы.',
            ]))
            ->all();
    }

    private function createCourses(array $teachers): array
    {
        $titles = [
            'Основы веб-разработки',
            'Практика PHP и Laravel',
            'Базы данных и SQL',
            'Интерфейсы веб-приложений',
            'Анализ данных',
            'Основы машинного обучения',
            'Информационная безопасность',
            'Тестирование программного обеспечения',
            'Управление проектами',
            'Цифровые образовательные инструменты',
            'Алгоритмы и структуры данных',
            'Выпускной квалификационный проект',
        ];

        return collect($titles)
            ->map(function (string $title, int $index) use ($teachers) {
                $teacher = $teachers[$index % count($teachers)];

                return Course::create([
                    'title' => '[ДЕМО] ' . $title,
                    'description' => 'Демонстрационный курс с материалами, тестами, студентами и историей активности.',
                    'teacher_id' => $teacher->id,
                ]);
            })
            ->all();
    }

    private function createMaterials(array $courses, array $teachers): void
    {
        foreach ($courses as $courseIndex => $course) {
            for ($index = 1; $index <= 6; $index++) {
                Material::create([
                    'course_id' => $course->id,
                    'author_id' => $teachers[$courseIndex % count($teachers)]->id,
                    'title' => sprintf('Материал %02d: %s', $index, Str::after($course->title, '[ДЕМО] ')),
                    'content' => implode("\n\n", [
                        '## Цели занятия',
                        'Материал объясняет тему через практическое задание, контрольный список и короткие вопросы для самопроверки.',
                        '## Практика',
                        'Студентам нужно изучить теорию, выполнить упражнение и подготовить вопросы к занятию.',
                    ]),
                    'images' => [],
                    'is_published' => true,
                ]);
            }
        }
    }

    private function createTestsWithQuestions(array $courses, array $teachers): array
    {
        $tests = [];
        $topics = ['Теория', 'Практика', 'Терминология', 'Решение задач', 'Разбор кейсов'];

        foreach ($courses as $courseIndex => $course) {
            for ($testIndex = 1; $testIndex <= 4; $testIndex++) {
                $test = Test::create([
                    'course_id' => $course->id,
                    'author_id' => $teachers[$courseIndex % count($teachers)]->id,
                    'title' => sprintf('Контрольный тест %02d: %s', $testIndex, Str::after($course->title, '[ДЕМО] ')),
                    'description' => 'Опубликованный демонстрационный тест с вопросами и вариантами ответов.',
                    'time_limit' => 35 + ($testIndex * 5),
                    'attempts_limit' => 3,
                    'is_published' => true,
                    'is_archived' => false,
                ]);

                for ($questionIndex = 1; $questionIndex <= 8; $questionIndex++) {
                    $question = Question::create([
                        'test_id' => $test->id,
                        'question_text' => sprintf('Вопрос %02d по теме "%s"', $questionIndex, $test->title),
                        'topic' => $topics[($questionIndex + $testIndex) % count($topics)],
                        'question_type' => 'single',
                        'points' => 1,
                    ]);

                    for ($answerIndex = 1; $answerIndex <= 4; $answerIndex++) {
                        Answer::create([
                            'question_id' => $question->id,
                            'answer_text' => sprintf('Вариант ответа %d', $answerIndex),
                            'is_correct' => $answerIndex === (($questionIndex % 4) + 1),
                        ]);
                    }
                }

                $tests[] = $test;
            }
        }

        return $tests;
    }

    private function createStudents(array $groups, array $courses): array
    {
        $firstNames = ['Алексей', 'Мария', 'Иван', 'София', 'Никита', 'Дарья', 'Павел', 'Алина', 'Кирилл', 'Полина'];
        $lastNames = ['Смирнов', 'Иванова', 'Кузнецов', 'Соколова', 'Попов', 'Волкова', 'Морозов', 'Новикова'];
        $goals = ['score_60', 'score_70', 'score_80', 'score_90'];
        $students = [];

        for ($index = 1; $index <= self::STUDENTS_COUNT; $index++) {
            $student = $this->createUser(
                sprintf('demo.student%03d@example.com', $index),
                sprintf('%s %s', $firstNames[$index % count($firstNames)], $lastNames[$index % count($lastNames)]),
                'student',
                [
                    'class_group_id' => $groups[($index - 1) % count($groups)]->id,
                    'learning_goal' => $goals[$index % count($goals)],
                ]
            );

            $courseCount = 3 + ($index % 4);
            $offset = $index % count($courses);
            $courseIds = collect(range(0, $courseCount - 1))
                ->map(fn (int $step) => $courses[($offset + $step) % count($courses)]->id)
                ->all();

            $student->courses()->sync($courseIds);
            $students[] = $student;
        }

        return $students;
    }

    private function createAttempts(array $students, array $tests): void
    {
        foreach ($students as $index => $student) {
            $profile = $this->studentProfile($index);
            $availableTests = collect($tests)
                ->filter(fn (Test $test) => $student->courses->contains('id', $test->course_id))
                ->values();

            $attemptsCount = min($availableTests->count(), $profile['attempts']);

            for ($attemptIndex = 0; $attemptIndex < $attemptsCount; $attemptIndex++) {
                $test = $availableTests[$attemptIndex % $availableTests->count()];
                $score = $this->scoreForProfile($profile['level'], $attemptIndex, $index);
                $createdAt = Carbon::now()->subDays($profile['days_since_activity'] + $attemptsCount - $attemptIndex);

                $attempt = TestAttempt::create([
                    'test_id' => $test->id,
                    'user_id' => $student->id,
                    'score' => $score,
                    'max_score' => 8,
                    'started_at' => $createdAt->copy()->subMinutes(45),
                    'finished_at' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $this->createStudentAnswers($attempt, $test, $score);
            }
        }
    }

    private function createStudentAnswers(TestAttempt $attempt, Test $test, int $score): void
    {
        $correctLeft = $score;

        foreach ($test->questions()->with('answers')->get() as $question) {
            $isCorrect = $correctLeft > 0;
            $answer = $isCorrect
                ? $question->answers->firstWhere('is_correct', true)
                : $question->answers->firstWhere('is_correct', false);

            StudentAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'answer_id' => $answer?->id,
                'text_answer' => null,
                'is_correct' => $isCorrect,
                'points_awarded' => $isCorrect ? 1 : 0,
            ]);

            if ($isCorrect) {
                $correctLeft--;
            }
        }
    }

    private function createSupportTickets(array $students, array $courses, array $teachers): void
    {
        $types = SupportTicket::typeOptions();
        $statuses = SupportTicket::statusOptions();

        foreach ($students as $index => $student) {
            $ticketCount = match (true) {
                $index % 11 === 0 => 4,
                $index % 7 === 0 => 2,
                $index % 5 === 0 => 1,
                default => 0,
            };

            for ($ticketIndex = 1; $ticketIndex <= $ticketCount; $ticketIndex++) {
                SupportTicket::create([
                    'user_id' => $student->id,
                    'course_id' => $courses[($index + $ticketIndex) % count($courses)]->id,
                    'assigned_teacher_id' => $teachers[($index + $ticketIndex) % count($teachers)]->id,
                    'type' => $types[($index + $ticketIndex) % count($types)],
                    'subject' => sprintf('Демо-обращение студента %03d-%d', $index + 1, $ticketIndex),
                    'message' => 'Студент просит помочь с темой, заданием или технической проблемой на платформе.',
                    'status' => $statuses[($index + $ticketIndex) % count($statuses)],
                    'created_at' => Carbon::now()->subDays(($index + $ticketIndex) % 35),
                    'updated_at' => Carbon::now()->subDays(($index + $ticketIndex) % 20),
                ]);
            }
        }
    }

    private function createMessages(array $students, array $teachers): void
    {
        foreach ($students as $index => $student) {
            if ($index % 3 !== 0) {
                continue;
            }

            $teacher = $teachers[$index % count($teachers)];

            Message::create([
                'sender_id' => $student->id,
                'recipient_id' => $teacher->id,
                'body' => 'Здравствуйте, мне нужна обратная связь по последней попытке прохождения теста.',
                'is_read' => $index % 2 === 0,
                'created_at' => Carbon::now()->subDays($index % 14),
                'updated_at' => Carbon::now()->subDays($index % 14),
            ]);

            Message::create([
                'sender_id' => $teacher->id,
                'recipient_id' => $student->id,
                'body' => 'Пожалуйста, повторите материал курса и попробуйте пройти тренировочный тест еще раз.',
                'is_read' => $index % 4 !== 0,
                'created_at' => Carbon::now()->subDays(($index % 14) - 1)->max(Carbon::now()->subDays(13)),
                'updated_at' => Carbon::now()->subDays(($index % 14) - 1)->max(Carbon::now()->subDays(13)),
            ]);
        }
    }

    private function createNotifications(array $students, array $teachers): void
    {
        foreach ($students as $index => $student) {
            if ($index % 2 !== 0) {
                continue;
            }

            Notification::create([
                'user_id' => $student->id,
                'title' => 'Новое демонстрационное задание',
                'body' => 'Преподаватель опубликовал новый тест по вашему курсу.',
                'type' => 'задание',
                'related_user_id' => $teachers[$index % count($teachers)]->id,
                'action_url' => '/tests',
                'is_read' => $index % 4 === 0,
            ]);
        }
    }

    private function createSchedule(array $groups, array $courses, array $teachers): void
    {
        $types = ScheduleEvent::typeOptions();

        foreach ($groups as $groupIndex => $group) {
            for ($week = 0; $week < 3; $week++) {
                for ($slot = 0; $slot < 3; $slot++) {
                    $startsAt = Carbon::now()
                        ->startOfWeek()
                        ->addWeeks($week)
                        ->addDays($slot + 1)
                        ->setTime(10 + $slot * 2, 0);

                    ScheduleEvent::create([
                        'teacher_id' => $teachers[($groupIndex + $slot) % count($teachers)]->id,
                        'class_group_id' => $group->id,
                        'course_id' => $courses[($groupIndex + $slot) % count($courses)]->id,
                        'title' => sprintf('Демо-занятие %s №%d', $group->name, $slot + 1),
                        'description' => 'Запланированное демонстрационное занятие для проверки календаря.',
                        'type' => $types[($groupIndex + $slot) % count($types)],
                        'location' => 'Аудитория ' . (200 + $groupIndex + $slot),
                        'starts_at' => $startsAt,
                        'ends_at' => $startsAt->copy()->addMinutes(90),
                    ]);
                }
            }
        }
    }

    private function createInvitationCodes(User $admin, array $groups): void
    {
        foreach ($groups as $index => $group) {
            InvitationCode::create([
                'code' => sprintf('DEMO-STUDENT-%02d', $index + 1),
                'role' => InvitationCode::ROLE_STUDENT,
                'class_group_id' => $group->id,
                'created_by' => $admin->id,
                'is_active' => true,
                'uses_count' => ($index + 1) * 2,
                'last_used_at' => Carbon::now()->subDays($index),
            ]);
        }

        InvitationCode::create([
            'code' => 'DEMO-TEACHER',
            'role' => InvitationCode::ROLE_TEACHER,
            'class_group_id' => null,
            'created_by' => $admin->id,
            'is_active' => true,
            'uses_count' => 1,
            'last_used_at' => Carbon::now()->subDays(2),
        ]);
    }

    private function createActionLogs(User $admin, array $teachers, array $students): void
    {
        ActionLog::create([
            'user_id' => $admin->id,
            'action' => 'Демо: генерация базы',
            'description' => 'Сформирована большая демонстрационная база данных.',
            'ip_address' => '127.0.0.1',
        ]);

        foreach ($teachers as $teacher) {
            ActionLog::create([
                'user_id' => $teacher->id,
                'action' => 'Демо: вход преподавателя',
                'description' => 'Преподаватель открыл панель управления.',
                'ip_address' => '127.0.0.1',
            ]);
        }

        foreach (array_slice($students, 0, 40) as $student) {
            ActionLog::create([
                'user_id' => $student->id,
                'action' => 'Демо: активность студента',
                'description' => 'Студент выполнил демонстрационную учебную активность.',
                'ip_address' => '127.0.0.1',
            ]);
        }
    }

    private function createUser(string $email, string $name, string $role, array $extra = []): User
    {
        return User::create(array_merge([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(self::PASSWORD),
            'role' => $role,
            'learning_goal' => 'score_80',
        ], $extra));
    }

    private function studentProfile(int $index): array
    {
        return match (true) {
            $index % 10 === 0 => ['level' => 'high_risk', 'attempts' => 4, 'days_since_activity' => 28],
            $index % 6 === 0 => ['level' => 'weak', 'attempts' => 7, 'days_since_activity' => 18],
            $index % 4 === 0 => ['level' => 'average', 'attempts' => 10, 'days_since_activity' => 8],
            default => ['level' => 'strong', 'attempts' => 14, 'days_since_activity' => 2],
        };
    }

    private function scoreForProfile(string $level, int $attemptIndex, int $studentIndex): int
    {
        $base = match ($level) {
            'high_risk' => 2 + ($attemptIndex % 3),
            'weak' => 4 + ($attemptIndex % 2),
            'average' => 5 + ($attemptIndex % 3),
            default => 6 + ($attemptIndex % 3),
        };

        $trend = match ($level) {
            'high_risk' => -intdiv($attemptIndex, 3),
            'weak' => $attemptIndex % 4 === 0 ? -1 : 0,
            'average' => intdiv($attemptIndex, 5),
            default => intdiv($attemptIndex, 4),
        };

        return max(0, min(8, $base + $trend + ($studentIndex % 2)));
    }
}
