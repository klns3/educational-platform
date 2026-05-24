<x-app-layout>
    <div class="min-h-screen bg-[#f6f8fc] text-slate-950 transition-colors duration-300 dark:bg-[#07111f] dark:text-white">
        <div class="relative overflow-hidden">

            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <section class="mb-7">
                    <p class="mb-4 inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:border-blue-400/20 dark:bg-blue-500/10 dark:text-blue-200">
                        Сообщения
                    </p>

                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                        Чат
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold text-slate-500 sm:text-base dark:text-slate-300">
                        Личные сообщения между пользователями платформы.
                    </p>
                </section>

                <div class="grid h-[720px] min-h-0 grid-cols-1 overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 md:grid-cols-12 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
                    <aside class="flex min-h-0 flex-col border-b border-slate-100 bg-slate-50/70 md:col-span-4 md:border-b-0 md:border-r lg:col-span-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <div class="border-b border-slate-100 p-4 dark:border-white/10">
                            <form method="GET" action="{{ route('messages.index') }}">
                                <input type="text"
                                       name="search"
                                       value="{{ $search ?? '' }}"
                                       placeholder="Поиск пользователя..."
                                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white">
                            </form>
                        </div>

                        <div class="flex-1 overflow-y-auto">
                            @forelse($users as $chatUser)
                                <a href="{{ route('messages.chat', ['user' => $chatUser, 'search' => $search]) }}"
                                   class="flex items-center gap-3 border-b border-slate-100 px-4 py-4 transition hover:bg-blue-50 dark:border-white/10 dark:hover:bg-blue-500/10
                                   {{ $activeUser && $activeUser->id === $chatUser->id ? 'bg-blue-50 dark:bg-blue-500/10' : '' }}">

                                    <div class="relative shrink-0">
                                        @if($chatUser->avatar_url)
                                            <img src="{{ $chatUser->avatar_url }}"
                                                 alt="{{ $chatUser->name }}"
                                                 class="h-12 w-12 rounded-2xl border border-slate-200 object-cover dark:border-white/10">
                                        @else
                                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 font-black text-white">
                                                {{ $chatUser->initials }}
                                            </div>
                                        @endif

                                        @if($chatUser->unread_messages_count > 0)
                                            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs font-black text-white">
                                                {{ $chatUser->unread_messages_count }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="truncate font-black text-slate-950 dark:text-white">
                                                {{ $chatUser->name }}
                                            </p>

                                            @if($chatUser->last_message)
                                                <span class="shrink-0 text-[11px] font-bold text-slate-400">
                                                    {{ $chatUser->last_message->created_at->format('H:i') }}
                                                </span>
                                            @endif
                                        </div>

                                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                            @if($chatUser->role === 'admin')
                                                Администратор
                                            @elseif($chatUser->role === 'teacher')
                                                Преподаватель
                                            @else
                                                Студент
                                            @endif
                                        </p>

                                        <p class="mt-1 truncate text-sm font-semibold text-slate-500 dark:text-slate-300">
                                            @if($chatUser->last_message)
                                                @if($chatUser->last_message->sender_id === auth()->id())
                                                    Вы:
                                                @endif
                                                {{ $chatUser->last_message->body }}
                                            @else
                                                Нет сообщений
                                            @endif
                                        </p>
                                    </div>
                                </a>
                            @empty
                                <div class="p-6 text-sm font-bold text-slate-500 dark:text-slate-400">
                                    Пользователи не найдены.
                                </div>
                            @endforelse
                        </div>
                    </aside>

                    <section class="flex min-h-0 flex-col overflow-hidden bg-white md:col-span-8 lg:col-span-9 dark:bg-transparent">
                        @if($activeUser)
                            <div class="flex items-center gap-4 border-b border-slate-100 bg-white/70 p-5 dark:border-white/10 dark:bg-white/[0.03]">
                                @if($activeUser->avatar_url)
                                    <img src="{{ $activeUser->avatar_url }}"
                                         alt="{{ $activeUser->name }}"
                                         class="h-12 w-12 rounded-2xl border border-slate-200 object-cover dark:border-white/10">
                                @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 font-black text-white">
                                        {{ $activeUser->initials }}
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <h2 class="truncate text-xl font-black text-slate-950 dark:text-white">
                                        {{ $activeUser->name }}
                                    </h2>

                                    <p class="truncate text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        {{ $activeUser->email }}
                                    </p>
                                </div>
                            </div>

                            <div id="chatMessages"
                                 data-fetch-url="{{ route('messages.fetch', $activeUser) }}"
                                 data-typing-url="{{ route('messages.typing', $activeUser) }}"
                                 class="min-h-0 flex-1 space-y-4 overflow-y-auto p-6">
                            </div>

                            <form id="chatForm"
                                  action="{{ route('messages.send', $activeUser) }}"
                                  method="POST"
                                  class="shrink-0 border-t border-slate-100 bg-slate-50/70 p-5 dark:border-white/10 dark:bg-white/[0.03]">
                                @csrf

                                <div class="flex gap-3">
                                    <textarea id="messageInput"
                                              name="body"
                                              rows="1"
                                              class="flex-1 resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                                              placeholder="Введите сообщение..."
                                              required></textarea>

                                    <button type="submit"
                                            class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
                                        Отправить
                                    </button>
                                </div>

                                <p id="chatError" class="mt-2 hidden text-sm font-bold text-red-500"></p>
                            </form>
                        @else
                            <div class="flex flex-1 items-center justify-center p-8">
                                <div class="max-w-md text-center">
                                    <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-[1.7rem] bg-blue-50 text-4xl dark:bg-blue-500/15">
                                        💬
                                    </div>

                                    <h2 class="text-2xl font-black text-slate-950 dark:text-white">
                                        Выберите пользователя
                                    </h2>

                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        Найдите пользователя слева и откройте переписку.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </section>
                </div>
            </div>
        </div>
    </div>

    @if($activeUser)
        <script>
            const chatMessages = document.getElementById('chatMessages');
            const chatForm = document.getElementById('chatForm');
            const messageInput = document.getElementById('messageInput');
            const chatError = document.getElementById('chatError');

            const fetchUrl = chatMessages.dataset.fetchUrl;
            const typingUrl = chatMessages.dataset.typingUrl;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            let lastMessagesHash = '';
            let isFetching = false;
            let typingPulseTimeout = null;
            let typingStopTimeout = null;
            let isTyping = false;

            function shouldScrollToBottom() {
                return chatMessages.scrollHeight - chatMessages.scrollTop - chatMessages.clientHeight < 120;
            }

            function scrollToBottom() {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            function renderTypingIndicator(show) {
                let indicator = document.getElementById('typingIndicator');

                if (!show) {
                    indicator?.remove();
                    return;
                }

                if (!indicator) {
                    indicator = document.createElement('div');
                    indicator.id = 'typingIndicator';
                    indicator.className = 'flex justify-start';
                    indicator.innerHTML = `
                        <div class="max-w-[75%]">
                            <div class="inline-flex items-center gap-3 rounded-2xl rounded-bl-md bg-slate-100 px-4 py-3 text-slate-500 shadow-sm dark:bg-white/[0.06] dark:text-slate-300">
                                <span class="text-sm font-bold">Печатает...</span>
                                <span class="flex items-center gap-1">
                                    <span class="h-2 w-2 animate-bounce rounded-full bg-slate-400 [animation-delay:-0.3s]"></span>
                                    <span class="h-2 w-2 animate-bounce rounded-full bg-slate-400 [animation-delay:-0.15s]"></span>
                                    <span class="h-2 w-2 animate-bounce rounded-full bg-slate-400"></span>
                                </span>
                            </div>
                        </div>
                    `;
                }

                chatMessages.appendChild(indicator);
            }

            function renderMessages(messages, isTypingRemote = false) {
                if (messages.length === 0) {
                    chatMessages.innerHTML = `
                        <div class="flex h-full items-center justify-center">
                            <div class="text-center">
                                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-[1.4rem] bg-blue-50 text-3xl dark:bg-blue-500/15">
                                    💬
                                </div>
                                <p class="font-black text-slate-950 dark:text-white">Сообщений пока нет</p>
                                <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">Напишите первое сообщение.</p>
                            </div>
                        </div>
                    `;
                    renderTypingIndicator(isTypingRemote);
                    return;
                }

                let html = '';

                messages.forEach(message => {
                    const isMine = message.is_mine;
                    const status = isMine
                        ? (message.is_read
                            ? '<span class="text-emerald-500">✓✓ прочитано</span>'
                            : '<span class="text-slate-400">✓ отправлено</span>')
                        : '';

                    const deleteButton = isMine
                        ? `
                            <button type="button"
                                    data-delete-url="${message.delete_url}"
                                    class="delete-message-btn font-bold text-red-400 opacity-0 transition hover:text-red-300 group-hover:opacity-100">
                                удалить
                            </button>
                        `
                        : '';

                    html += `
                        <div class="group flex ${isMine ? 'justify-end' : 'justify-start'}">
                            <div class="flex max-w-[75%] gap-3 ${isMine ? 'flex-row-reverse' : ''}">
                                <div>
                                    <div class="rounded-2xl px-5 py-3 shadow-sm ${isMine ? 'rounded-br-md bg-blue-600 text-white shadow-blue-600/20' : 'rounded-bl-md bg-slate-100 text-slate-800 dark:bg-white/[0.06] dark:text-slate-100'}">
                                        <p class="whitespace-pre-line break-words text-sm font-semibold leading-6">${message.body}</p>
                                    </div>

                                    <div class="mt-1 flex items-center gap-2 text-xs font-semibold text-slate-400 ${isMine ? 'justify-end' : 'justify-start'}">
                                        <span>${message.created_at}</span>
                                        ${status}
                                        ${deleteButton}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                chatMessages.innerHTML = html;
                renderTypingIndicator(isTypingRemote);
            }

            async function sendTypingStatus(nextState) {
                if (nextState === isTyping) {
                    return;
                }

                isTyping = nextState;

                try {
                    await fetch(typingUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ is_typing: nextState }),
                    });
                } catch (error) {
                    console.error(error);
                }
            }

            function queueTypingPulse() {
                clearTimeout(typingPulseTimeout);
                clearTimeout(typingStopTimeout);

                sendTypingStatus(true);

                typingPulseTimeout = setTimeout(() => {
                    isTyping = false;
                    sendTypingStatus(true);
                }, 3000);

                typingStopTimeout = setTimeout(() => {
                    sendTypingStatus(false);
                }, 3500);
            }

            async function loadMessages(forceScroll = false) {
                if (isFetching) return;

                isFetching = true;

                try {
                    const response = await fetch(fetchUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Ошибка загрузки сообщений');
                    }

                    const data = await response.json();
                    const newHash = JSON.stringify(data.messages);

                    if (newHash !== lastMessagesHash) {
                        const needScroll = forceScroll || shouldScrollToBottom();

                        renderMessages(data.messages, data.is_typing);
                        lastMessagesHash = newHash;

                        if (needScroll) {
                            scrollToBottom();
                        }
                    } else {
                        renderTypingIndicator(data.is_typing);
                    }
                } catch (error) {
                    console.error(error);
                } finally {
                    isFetching = false;
                }
            }

            chatForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                const body = messageInput.value.trim();

                if (!body) {
                    return;
                }

                chatError.classList.add('hidden');
                chatError.textContent = '';

                try {
                    const response = await fetch(chatForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ body }),
                    });

                    if (!response.ok) {
                        throw new Error('Сообщение не отправлено');
                    }

                    messageInput.value = '';
                    clearTimeout(typingPulseTimeout);
                    clearTimeout(typingStopTimeout);
                    await sendTypingStatus(false);
                    await loadMessages(true);
                } catch (error) {
                    chatError.textContent = 'Не удалось отправить сообщение';
                    chatError.classList.remove('hidden');
                }
            });

            chatMessages.addEventListener('click', async function (event) {
                const button = event.target.closest('.delete-message-btn');

                if (!button) {
                    return;
                }

                if (!confirm('Удалить сообщение?')) {
                    return;
                }

                try {
                    const response = await fetch(button.dataset.deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Ошибка удаления');
                    }

                    await loadMessages(true);
                } catch (error) {
                    alert('Не удалось удалить сообщение');
                }
            });

            messageInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    chatForm.dispatchEvent(new Event('submit'));
                }
            });

            messageInput.addEventListener('input', function () {
                if (messageInput.value.trim()) {
                    queueTypingPulse();
                    return;
                }

                clearTimeout(typingPulseTimeout);
                clearTimeout(typingStopTimeout);
                sendTypingStatus(false);
            });

            messageInput.addEventListener('blur', function () {
                clearTimeout(typingPulseTimeout);
                clearTimeout(typingStopTimeout);
                sendTypingStatus(false);
            });

            loadMessages(true);
            setInterval(loadMessages, 2000);
        </script>
    @endif
</x-app-layout>
