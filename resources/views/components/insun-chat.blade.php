<div id="insun-container" class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3">

    {{-- Chat Window --}}
    <div id="insun-chat-window" class="hidden flex flex-col w-80 h-[450px] bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-emerald-600 to-teal-500 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center text-lg">🤖</div>
                <div>
                    <p class="text-white font-semibold text-sm">INSUN AI</p>
                    <p class="text-emerald-200 text-xs">Asisten Akademik</p>
                </div>
            </div>
            <button onclick="toggleInsun()" class="text-white/70 hover:text-white text-xl leading-none">×</button>
        </div>

        {{-- Messages --}}
        <div id="insun-messages" class="flex-1 overflow-y-auto p-3 space-y-2 bg-gray-50">
            <div class="flex gap-2">
                <div class="w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center text-xs flex-shrink-0 mt-0.5">🤖</div>
                <div class="bg-white rounded-xl rounded-tl-none px-3 py-2 text-sm text-gray-700 shadow-sm max-w-[220px]">
                    Halo! Saya INSUN, asisten akademik virtual kamu. Tanya apa saja soal jadwal kuliah ya! 😊
                </div>
            </div>
        </div>

        {{-- Typing Indicator (hidden by default) --}}
        <div id="insun-typing" class="hidden px-3 pb-2">
            <div class="flex gap-2 items-center">
                <div class="w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center text-xs">🤖</div>
                <div class="bg-white rounded-xl rounded-tl-none px-3 py-2 shadow-sm">
                    <div class="flex gap-1">
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Suggestions --}}
        <div class="px-3 pb-2 flex gap-1 flex-wrap" id="suggestions">
            <button onclick="sendSuggestion(this)" class="text-xs px-2 py-1 bg-emerald-50 text-emerald-600 rounded-full border border-indigo-100 hover:bg-emerald-100 transition">Jadwal hari ini?</button>
            <button onclick="sendSuggestion(this)" class="text-xs px-2 py-1 bg-emerald-50 text-emerald-600 rounded-full border border-indigo-100 hover:bg-emerald-100 transition">Kelas yang berlangsung?</button>
            <button onclick="sendSuggestion(this)" class="text-xs px-2 py-1 bg-emerald-50 text-emerald-600 rounded-full border border-indigo-100 hover:bg-emerald-100 transition">Ruangan kosong?</button>
        </div>

        {{-- Input --}}
        <div class="border-t border-gray-100 px-3 py-2 bg-white flex gap-2">
            <input id="insun-input" type="text" placeholder="Tanyakan sesuatu..."
                class="flex-1 text-sm border border-gray-200 rounded-full px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent"
                maxlength="500"
                onkeydown="if(event.key==='Enter') sendInsun()">
            <button onclick="sendInsun()"
                class="w-8 h-8 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full flex items-center justify-center text-sm transition flex-shrink-0">
                ➤
            </button>
        </div>
    </div>

    {{-- FAB Toggle Button --}}
    <button onclick="toggleInsun()" id="insun-fab"
        class="w-14 h-14 bg-gradient-to-br from-emerald-600 to-teal-500 rounded-full shadow-lg hover:shadow-xl flex items-center justify-center text-2xl text-white transition hover:scale-105 active:scale-95">
        💬
    </button>
</div>

<script>
const insunWindow = document.getElementById('insun-chat-window');
const insunMessages = document.getElementById('insun-messages');
const insunTyping = document.getElementById('insun-typing');
const insunInput = document.getElementById('insun-input');
const insunFab = document.getElementById('insun-fab');
let insunOpen = false;

function toggleInsun() {
    insunOpen = !insunOpen;
    insunWindow.classList.toggle('hidden', !insunOpen);
    insunFab.textContent = insunOpen ? '✕' : '💬';
    if (insunOpen) insunInput.focus();
}

function formatMd(text) {
    let s = escHtml(text);
    s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    s = s.replace(/\n/g, '<br>');
    return s;
}

function appendMsg(text, isUser) {
    const div = document.createElement('div');
    div.className = 'flex gap-2' + (isUser ? ' justify-end' : '');
    const formatted = isUser ? escHtml(text) : formatMd(text);
    div.innerHTML = isUser
        ? `<div class="bg-emerald-600 text-white rounded-xl rounded-tr-none px-3 py-2 text-sm max-w-[220px] shadow-sm">${formatted}</div>`
        : `<div class="w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center text-xs flex-shrink-0 mt-0.5">🤖</div>
           <div class="bg-white rounded-xl rounded-tl-none px-3 py-2 text-sm text-gray-700 shadow-sm max-w-[250px] insun-reply">${formatted}</div>`;
    insunMessages.appendChild(div);
    insunMessages.scrollTop = insunMessages.scrollHeight;
}

function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function sendSuggestion(btn) {
    insunInput.value = btn.textContent.trim();
    sendInsun();
    document.getElementById('suggestions').classList.add('hidden');
}

async function sendInsun() {
    const msg = insunInput.value.trim();
    if (!msg) return;

    insunInput.value = '';
    appendMsg(msg, true);

    insunTyping.classList.remove('hidden');
    insunMessages.scrollTop = insunMessages.scrollHeight;

    try {
        const res = await fetch('{{ route("insun.chat") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: msg }),
        });
        const data = await res.json();
        // Simulated "thinking" delay for natural feel
        const delay = Math.min(800 + (data.reply || '').length * 3, 2000);
        await new Promise(r => setTimeout(r, delay));
        insunTyping.classList.add('hidden');
        appendMsg(data.reply || 'Maaf, tidak ada respons.', false);
    } catch (e) {
        insunTyping.classList.add('hidden');
        appendMsg('Waduh, sepertinya ada gangguan teknis nih. 😔 Coba lagi sebentar ya!', false);
    }
}
</script>
