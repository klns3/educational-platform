@php
    use App\Models\InvitationCode;

    $labels = [
        'heading' => 'Пригласительные коды',
        'subtitle' => 'Коды для регистрации учителей и учеников',
        'create' => 'Создать код',
        'role' => 'Роль',
        'group' => 'Группа',
        'code' => 'Код',
        'status' => 'Статус',
        'uses' => 'Использован',
        'creator' => 'Создал',
        'date' => 'Создан',
        'actions' => 'Действия',
        'toggle_on' => 'Включить',
        'toggle_off' => 'Отключить',
        'delete' => 'Удалить',
        'delete_confirm' => 'Удалить пригласительный код?',
        'student' => 'Ученик',
        'teacher' => 'Учитель',
        'active' => 'Активен',
        'inactive' => 'Отключён',
        'without_group' => 'Без группы',
        'group_placeholder' => 'Выберите группу',
        'group_required' => 'Для кода ученика группа обязательна',
        'group_not_needed' => 'Для кода учителя группа не нужна',
        'empty' => 'Кодов пока нет',
    ];
@endphp

<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Администрирование
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        {{ $labels['heading'] }}
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        {{ $labels['subtitle'] }}
                    </p>
                </section>

                @if(session('success'))
                    <div class="mb-6 rounded-[1.4rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 rounded-[1.4rem] border border-red-200 bg-red-50 px-5 py-4 text-sm font-black text-red-700 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-200">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-8 overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">
                            Создание кода
                        </h2>
                    </div>

                    <form method="POST" action="{{ route('invitation-codes.store') }}" class="grid grid-cols-1 gap-4 p-6 md:grid-cols-4">
                        @csrf

                        <div>
                            <label for="role" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                {{ $labels['role'] }}
                            </label>

                            <select id="role"
                                    name="role"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                <option value="{{ InvitationCode::ROLE_STUDENT }}" @selected(old('role', InvitationCode::ROLE_STUDENT) === InvitationCode::ROLE_STUDENT)>
                                    {{ $labels['student'] }}
                                </option>
                                <option value="{{ InvitationCode::ROLE_TEACHER }}" @selected(old('role') === InvitationCode::ROLE_TEACHER)>
                                    {{ $labels['teacher'] }}
                                </option>
                            </select>
                        </div>

                        <div id="groupField" class="md:col-span-2">
                            <label for="class_group_id" class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                {{ $labels['group'] }}
                            </label>

                            <select id="class_group_id"
                                    name="class_group_id"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                                <option value="">{{ $labels['group_placeholder'] }}</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" @selected((int) old('class_group_id') === $group->id)>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>

                            <p id="groupHint" class="mt-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                {{ $labels['group_required'] }}
                            </p>
                        </div>

                        <div class="flex items-end">
                            <button class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                {{ $labels['create'] }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1050px] text-sm">
                            <thead class="border-b border-slate-100 bg-slate-50 text-slate-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-400">
                                <tr>
                                    <th class="px-5 py-4 text-left font-black">{{ $labels['code'] }}</th>
                                    <th class="px-5 py-4 text-left font-black">{{ $labels['role'] }}</th>
                                    <th class="px-5 py-4 text-left font-black">{{ $labels['group'] }}</th>
                                    <th class="px-5 py-4 text-left font-black">{{ $labels['status'] }}</th>
                                    <th class="px-5 py-4 text-left font-black">{{ $labels['uses'] }}</th>
                                    <th class="px-5 py-4 text-left font-black">{{ $labels['creator'] }}</th>
                                    <th class="px-5 py-4 text-left font-black">{{ $labels['date'] }}</th>
                                    <th class="px-5 py-4 text-right font-black">{{ $labels['actions'] }}</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                                @forelse($codes as $code)
                                    <tr class="transition hover:bg-slate-50 dark:hover:bg-white/[0.03]">
                                        <td class="px-5 py-4 font-mono font-black text-slate-950 dark:text-white">
                                            {{ $code->code }}
                                        </td>

                                        <td class="px-5 py-4 font-bold text-slate-700 dark:text-slate-300">
                                            {{ $labels[$code->role] }}
                                        </td>

                                        <td class="px-5 py-4 font-bold text-slate-700 dark:text-slate-300">
                                            {{ $code->classGroup?->name ?? $labels['without_group'] }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $code->is_active ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300' }}">
                                                {{ $code->is_active ? $labels['active'] : $labels['inactive'] }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 font-bold text-slate-700 dark:text-slate-300">
                                            {{ $code->uses_count }}
                                        </td>

                                        <td class="px-5 py-4 font-bold text-slate-700 dark:text-slate-300">
                                            {{ $code->creator?->name ?? '—' }}
                                        </td>

                                        <td class="px-5 py-4 font-bold text-slate-500 dark:text-slate-400">
                                            {{ $code->created_at->format('d.m.Y H:i') }}
                                        </td>

                                        <td class="px-5 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <form method="POST" action="{{ route('invitation-codes.toggle', $code) }}">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
                                                        {{ $code->is_active ? $labels['toggle_off'] : $labels['toggle_on'] }}
                                                    </button>
                                                </form>

                                                <form method="POST"
                                                      action="{{ route('invitation-codes.destroy', $code) }}"
                                                      onsubmit="return confirm('{{ $labels['delete_confirm'] }}')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-600 transition hover:border-red-400 hover:bg-red-100 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/15">
                                                        {{ $labels['delete'] }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-5 py-12 text-center">
                                            <p class="font-black text-slate-950 dark:text-white">
                                                {{ $labels['empty'] }}
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    (function () {
        const roleField = document.getElementById('role');
        const groupField = document.getElementById('groupField');
        const groupSelect = document.getElementById('class_group_id');
        const groupHint = document.getElementById('groupHint');
        const studentRole = '{{ InvitationCode::ROLE_STUDENT }}';
        const requiredText = @json($labels['group_required']);
        const optionalText = @json($labels['group_not_needed']);

        if (!roleField || !groupField || !groupSelect || !groupHint) {
            return;
        }

        function syncGroupState() {
            const isStudent = roleField.value === studentRole;

            groupSelect.required = isStudent;
            groupSelect.disabled = !isStudent;
            groupField.classList.toggle('opacity-60', !isStudent);
            groupHint.textContent = isStudent ? requiredText : optionalText;

            if (!isStudent) {
                groupSelect.value = '';
            }
        }

        roleField.addEventListener('change', syncGroupState);
        syncGroupState();
    })();
</script>