<?php

use App\Http\Controllers\ActionLogController;
use App\Http\Controllers\ClassGroupController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationCodeController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TestPassingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiAnalyticsController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    $role = auth()->user()->role;

    if ($role === null) {
        return redirect()->route('pending.role');
    }

    if ($role === 'admin') {
        return redirect()->route('dashboard.admin');
    }

    if ($role === 'teacher') {
        return redirect()->route('dashboard.teacher');
    }

    if ($role === 'student') {
        return redirect()->route('dashboard.student');
    }

    return redirect()->route('pending.role');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('/pending-role', function () {
        if (auth()->user()->role !== null) {
            return redirect()->route('dashboard');
        }

        return view('auth.pending-role');
    })->name('pending.role');

    Route::middleware(['auth', 'role:admin,teacher'])->group(function () {
    Route::get('/ai-analytics', [AiAnalyticsController::class, 'index'])
        ->name('ai-analytics.index');
    });
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('dashboard.admin');

    Route::get('/dashboard/teacher', [DashboardController::class, 'teacher'])
        ->middleware('role:teacher')
        ->name('dashboard.teacher');

    Route::get('/dashboard/student', [DashboardController::class, 'student'])
        ->middleware('role:student')
        ->name('dashboard.student');

    Route::get('/digital-curator', [DashboardController::class, 'digitalCurator'])
        ->middleware('role:student')
        ->name('digital-curator.index');

    Route::post('/digital-curator/goal', [DashboardController::class, 'updateLearningGoal'])
        ->middleware('role:student')
        ->name('digital-curator.goal.update');

    Route::post('/digital-curator/help-request', [DashboardController::class, 'requestCuratorHelp'])
        ->middleware('role:student')
        ->name('digital-curator.help-request');

    Route::get('/teacher/digital-curator', [DashboardController::class, 'teacherDigitalCurator'])
        ->middleware('role:admin,teacher')
        ->name('teacher-curator.index');

    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');

        Route::get('/action-logs', [ActionLogController::class, 'index'])->name('action-logs.index');

        Route::get('/class-groups', [ClassGroupController::class, 'index'])->name('class-groups.index');
        Route::get('/class-groups/create', [ClassGroupController::class, 'create'])->name('class-groups.create');
        Route::post('/class-groups', [ClassGroupController::class, 'store'])->name('class-groups.store');
        Route::get('/class-groups/{classGroup}/edit', [ClassGroupController::class, 'edit'])->name('class-groups.edit');
        Route::put('/class-groups/{classGroup}', [ClassGroupController::class, 'update'])->name('class-groups.update');
        Route::delete('/class-groups/{classGroup}', [ClassGroupController::class, 'destroy'])->name('class-groups.destroy');

        Route::get('/invitation-codes', [InvitationCodeController::class, 'index'])->name('invitation-codes.index');
        Route::post('/invitation-codes', [InvitationCodeController::class, 'store'])->name('invitation-codes.store');
        Route::patch('/invitation-codes/{invitationCode}/toggle', [InvitationCodeController::class, 'toggle'])->name('invitation-codes.toggle');
        Route::delete('/invitation-codes/{invitationCode}', [InvitationCodeController::class, 'destroy'])->name('invitation-codes.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,teacher,student')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
        Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
        Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    Route::get('/notifications/broadcast', [NotificationController::class, 'broadcastCreate'])
        ->middleware('role:admin,teacher')
        ->name('notifications.broadcast.create');

    Route::post('/notifications/broadcast', [NotificationController::class, 'broadcastStore'])
        ->middleware('role:admin,teacher')
        ->name('notifications.broadcast.store');

    /*
    |--------------------------------------------------------------------------
    | Messenger
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,teacher,student')->group(function () {
        Route::get('/messages/unread-count', [MessageController::class, 'unreadCount'])->name('messages.unread-count');
        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/chat/{user}', [MessageController::class, 'chat'])->name('messages.chat');
        Route::get('/chat/{user}/messages', [MessageController::class, 'fetchMessages'])->name('messages.fetch');
        Route::post('/chat/{user}/typing', [MessageController::class, 'updateTypingStatus'])->name('messages.typing');
        Route::post('/chat/{user}', [MessageController::class, 'send'])->name('messages.send');
        Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Schedule
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,teacher,student')->group(function () {
        Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/schedule/create', [ScheduleController::class, 'create'])->name('schedule.create');
        Route::post('/schedule', [ScheduleController::class, 'store'])->name('schedule.store');
        Route::get('/schedule/{scheduleEvent}/edit', [ScheduleController::class, 'edit'])
            ->whereNumber('scheduleEvent')
            ->name('schedule.edit');
        Route::put('/schedule/{scheduleEvent}', [ScheduleController::class, 'update'])
            ->whereNumber('scheduleEvent')
            ->name('schedule.update');
        Route::delete('/schedule/{scheduleEvent}', [ScheduleController::class, 'destroy'])
            ->whereNumber('scheduleEvent')
            ->name('schedule.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Support tickets
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,teacher,student')->group(function () {
        Route::get('/support-tickets', [SupportTicketController::class, 'index'])->name('support-tickets.index');
        Route::get('/support-tickets/create', [SupportTicketController::class, 'create'])->name('support-tickets.create');
        Route::post('/support-tickets', [SupportTicketController::class, 'store'])->name('support-tickets.store');
        Route::get('/support-tickets/{supportTicket}', [SupportTicketController::class, 'show'])->name('support-tickets.show');
        Route::patch('/support-tickets/{supportTicket}', [SupportTicketController::class, 'update'])->name('support-tickets.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Results
    |--------------------------------------------------------------------------
    */

    Route::get('/my-results', [ResultController::class, 'myResults'])
        ->middleware('role:admin,teacher,student')
        ->name('results.my');

    Route::get('/tests/{test}/results', [ResultController::class, 'testResults'])
        ->middleware('role:admin,teacher')
        ->name('results.test');

    /*
    |--------------------------------------------------------------------------
    | Test passing
    |--------------------------------------------------------------------------
    */

    Route::get('/tests/{test}/take', [TestPassingController::class, 'take'])
        ->middleware('role:admin,teacher,student')
        ->name('tests.take');

    Route::post('/tests/{test}/submit', [TestPassingController::class, 'submit'])
        ->middleware('role:admin,teacher,student')
        ->name('tests.submit');

    Route::get('/attempts/{attempt}/result', [TestPassingController::class, 'result'])
        ->middleware('role:admin,teacher,student')
        ->name('tests.result');

    /*
    |--------------------------------------------------------------------------
    | Questions
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/tests/{test}/questions', [QuestionController::class, 'index'])->name('questions.index');
        Route::get('/tests/{test}/questions/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('/tests/{test}/questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Tests
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,teacher,student')->group(function () {
        Route::get('/courses/{course}/tests', [TestController::class, 'index'])->name('tests.index');
        Route::get('/tests/{test}', [TestController::class, 'show'])->name('tests.show');
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/courses/{course}/tests/create', [TestController::class, 'create'])->name('tests.create');
        Route::post('/courses/{course}/tests', [TestController::class, 'store'])->name('tests.store');
        Route::get('/tests/{test}/edit', [TestController::class, 'edit'])->name('tests.edit');
        Route::put('/tests/{test}', [TestController::class, 'update'])->name('tests.update');
        Route::delete('/tests/{test}', [TestController::class, 'destroy'])->name('tests.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Materials
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,teacher,student')->group(function () {
        Route::get('/courses/{course}/materials', [MaterialController::class, 'index'])->name('materials.index');
        Route::get('/materials/{material}', [MaterialController::class, 'show'])->name('materials.show');
        Route::get('/materials/{material}/pdf', [MaterialController::class, 'downloadPdf'])->name('materials.pdf');
        Route::get('/materials/{material}/file', [MaterialController::class, 'downloadAttachment'])->name('materials.file');
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/courses/{course}/materials/create', [MaterialController::class, 'create'])->name('materials.create');
        Route::post('/courses/{course}/materials', [MaterialController::class, 'store'])->name('materials.store');
        Route::get('/materials/{material}/edit', [MaterialController::class, 'edit'])->name('materials.edit');
        Route::put('/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
        Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Courses
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,teacher,student')->group(function () {
        Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}', [CourseController::class, 'show'])
            ->whereNumber('course')
            ->name('courses.show');
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])
            ->whereNumber('course')
            ->name('courses.edit');
        Route::put('/courses/{course}', [CourseController::class, 'update'])
            ->whereNumber('course')
            ->name('courses.update');
        Route::delete('/courses/{course}', [CourseController::class, 'destroy'])
            ->whereNumber('course')
            ->name('courses.destroy');
        Route::get('/courses/{course}/students', [CourseController::class, 'students'])
            ->whereNumber('course')
            ->name('courses.students');
        Route::put('/courses/{course}/students', [CourseController::class, 'syncStudents'])
            ->whereNumber('course')
            ->name('courses.students.sync');
    });

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
