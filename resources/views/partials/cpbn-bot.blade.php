{{--
    The CPBN Bot: CareerPath BN's guide to the website.

    It answers questions about using the site (what a page is for,
    how to do something, the account and plan rules, what to do
    next) and links to the pages it mentions. It is not the Career
    Adviser, which stays on its own page for career advice.

    The ring around its logo shows how much of the plan's token
    allowance has been used. A plan with CPBN Bot History can
    reopen past conversations; otherwise the panel keeps the
    current conversation for this browser session.
--}}
@php
    $cpbnBotCurrentPage = \App\Support\CpbnBotKnowledge::pageForRoute(request()->route()?->getName());
@endphp

<button
    type="button"
    class="cpbn-bot-fab"
    id="cpbnBotToggle"
    aria-controls="cpbnBot"
    aria-expanded="false"
    aria-label="Open CPBN Bot"
    title="CPBN Bot"
>
    <img
        src="{{ asset('images/careerpath-badge.png') }}?v={{ filemtime(public_path('images/careerpath-badge.png')) }}"
        alt=""
        class="cpbn-bot-fab-logo"
    >
</button>

<section
    class="cpbn-bot"
    id="cpbnBot"
    role="dialog"
    aria-label="CPBN Bot"
    hidden
    data-panel-url="{{ route('student.cpbn-bot.panel') }}"
    data-ask-url="{{ route('student.cpbn-bot.ask') }}"
    data-conversations-url="{{ route('student.cpbn-bot.conversations') }}"
    data-user="{{ Auth::id() }}"
    data-current-page="{{ $cpbnBotCurrentPage }}"
>
    <header class="cpbn-bot-head">
        <div class="cpbn-bot-ring" id="cpbnBotRing" title="Token allowance">
            <svg viewBox="0 0 36 36" aria-hidden="true">
                <circle class="cpbn-bot-ring-track" cx="18" cy="18" r="15.5"></circle>
                <circle class="cpbn-bot-ring-fill" id="cpbnBotRingFill" cx="18" cy="18" r="15.5"></circle>
            </svg>
            <img
                class="cpbn-bot-ring-logo"
                src="{{ asset('images/careerpath-badge.png') }}?v={{ filemtime(public_path('images/careerpath-badge.png')) }}"
                alt=""
            >
        </div>

        <div class="cpbn-bot-title">
            <strong>CPBN Bot</strong>
            <span id="cpbnBotUsage">Loading…</span>
        </div>

        <div class="cpbn-bot-actions">
            <button type="button" class="cpbn-bot-icon" id="cpbnBotHistory" title="Past conversations" aria-label="Past conversations" hidden>
                <i class="fas fa-clock-rotate-left"></i>
            </button>
            <button type="button" class="cpbn-bot-icon" id="cpbnBotNew" title="New conversation" aria-label="New conversation">
                <i class="fas fa-pen-to-square"></i>
            </button>
            <button type="button" class="cpbn-bot-icon" id="cpbnBotClose" title="Close" aria-label="Close CPBN Bot">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
    </header>

    <div class="cpbn-bot-list" id="cpbnBotList" hidden>
        <p class="cpbn-bot-list-title">Past conversations</p>
        <div id="cpbnBotListItems"></div>
    </div>

    <div class="cpbn-bot-messages" id="cpbnBotMessages" aria-live="polite">
        <div class="cpbn-bot-empty" id="cpbnBotEmpty">
            <p>
                Hi {{ Str::before(Auth::user()->name, ' ') }}! I'm the CPBN Bot, your guide to
                CareerPath BN. Ask me how to do something on the site and I'll take you there.
                For career advice, use the Career Adviser.
            </p>
            <div class="cpbn-bot-chips">
                @if($cpbnBotCurrentPage === 'profile_edit')
                    <button type="button" data-bot-suggest>Help me fill in my profile</button>
                @endif
                <button type="button" data-bot-suggest>What should I do next?</button>
                <button type="button" data-bot-suggest>How do I generate career recommendations?</button>
                <button type="button" data-bot-suggest>How do I upgrade or cancel Premium?</button>
            </div>
        </div>
    </div>

    <form class="cpbn-bot-composer" id="cpbnBotForm">
        <textarea
            id="cpbnBotInput"
            rows="1"
            maxlength="500"
            placeholder="Ask how to do something…"
            aria-label="Your question"
        ></textarea>
        <button type="submit" id="cpbnBotSend" aria-label="Send">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</section>

<style>
    /* Just the badge: no pill or circle behind it. */
    .cpbn-bot-fab {
        position: fixed;
        right: 22px;
        bottom: 22px;
        z-index: 900;
        display: block;
        width: 68px;
        height: 68px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: none;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .cpbn-bot-fab:hover,
    .cpbn-bot-fab[aria-expanded="true"] {
        transform: translateY(-2px) scale(1.04);
    }

    .cpbn-bot-fab:focus-visible {
        outline: 3px solid var(--accent, #c9a84c);
        outline-offset: 3px;
    }

    .cpbn-bot {
        position: fixed;
        right: 24px;
        bottom: 100px;
        z-index: 950;
        display: flex;
        flex-direction: column;
        width: 380px;
        height: min(560px, calc(100vh - 120px));
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 16px;
        background: var(--card, #ffffff);
        box-shadow: 0 20px 60px rgba(13, 31, 51, 0.25);
        overflow: hidden;
    }

    .cpbn-bot[hidden] {
        display: none;
    }

    .cpbn-bot-head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 12px 12px 14px;
        background: var(--primary, #1a3a5c);
        color: #ffffff;
    }

    .cpbn-bot-ring {
        position: relative;
        width: 40px;
        height: 40px;
        flex-shrink: 0;
    }

    .cpbn-bot-ring svg {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }

    .cpbn-bot-ring circle {
        fill: none;
        stroke-width: 3.5;
    }

    .cpbn-bot-ring-track {
        stroke: rgba(255, 255, 255, 0.18);
    }

    .cpbn-bot-ring-fill {
        stroke: #4ade80;
        stroke-linecap: round;
        stroke-dasharray: 97.4;
        stroke-dashoffset: 97.4;
        transition: stroke-dashoffset 0.4s ease, stroke 0.2s ease;
    }

    .cpbn-bot-ring.is-warning .cpbn-bot-ring-fill { stroke: #fbbf24; }
    .cpbn-bot-ring.is-danger .cpbn-bot-ring-fill { stroke: #f87171; }
    .cpbn-bot-ring.is-unlimited .cpbn-bot-ring-fill { stroke: var(--accent, #c9a84c); }

    .cpbn-bot-ring-logo {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        object-fit: contain;
        background: #ffffff;
        transform: translate(-50%, -50%);
    }

    .cpbn-bot-fab-logo {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: drop-shadow(0 6px 14px rgba(13, 31, 51, 0.35));
    }

    .cpbn-bot-links {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }

    .cpbn-bot-links a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 11px;
        border: 1px solid var(--primary, #1a3a5c);
        border-radius: 100px;
        background: var(--card, #ffffff);
        color: var(--primary, #1a3a5c);
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }

    .cpbn-bot-links a:hover {
        background: var(--primary, #1a3a5c);
        color: #ffffff;
    }

    .cpbn-bot-title {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        line-height: 1.3;
    }

    .cpbn-bot-title strong {
        font-size: 14px;
    }

    .cpbn-bot-title span {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.72);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cpbn-bot-actions {
        display: flex;
        gap: 2px;
    }

    .cpbn-bot-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border: none;
        border-radius: 8px;
        background: transparent;
        color: #ffffff;
        font-size: 13px;
        text-decoration: none;
        cursor: pointer;
    }

    .cpbn-bot-icon:hover,
    .cpbn-bot-icon.is-active {
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
    }

    .cpbn-bot-icon[hidden] {
        display: none;
    }

    .cpbn-bot-messages,
    .cpbn-bot-list {
        flex: 1;
        overflow-y: auto;
        padding: 14px;
        background: var(--bg, #f4f6f9);
    }

    .cpbn-bot-list[hidden],
    .cpbn-bot-messages[hidden] {
        display: none;
    }

    .cpbn-bot-list-title {
        margin: 0 0 10px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted, #6b7280);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .cpbn-bot-list-row {
        display: flex;
        align-items: stretch;
        gap: 6px;
        margin-bottom: 8px;
    }

    .cpbn-bot-list-delete {
        flex-shrink: 0;
        width: 36px;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 10px;
        background: var(--card, #ffffff);
        color: var(--text-muted, #6b7280);
        cursor: pointer;
    }

    .cpbn-bot-list-delete:hover,
    .cpbn-bot-list-delete.is-failed {
        border-color: #fca5a5;
        background: #fef2f2;
        color: #b91c1c;
    }

    .cpbn-bot-list-item {
        display: block;
        flex: 1;
        min-width: 0;
        padding: 10px 12px;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 10px;
        background: var(--card, #ffffff);
        color: var(--text, #1a1a2e);
        font-family: inherit;
        text-align: left;
        cursor: pointer;
    }

    .cpbn-bot-list-item:hover,
    .cpbn-bot-list-item.is-current {
        border-color: var(--primary, #1a3a5c);
    }

    .cpbn-bot-list-item strong {
        display: block;
        font-size: 13px;
        font-weight: 600;
    }

    .cpbn-bot-list-item span {
        font-size: 11px;
        color: var(--text-muted, #6b7280);
    }

    .cpbn-bot-msg {
        max-width: 88%;
        margin-bottom: 10px;
        padding: 10px 12px;
        border-radius: 12px;
        font-size: 13px;
        line-height: 1.55;
        word-wrap: break-word;
    }

    .cpbn-bot-msg.from-student {
        margin-left: auto;
        background: var(--primary, #1a3a5c);
        color: #ffffff;
        border-bottom-right-radius: 4px;
    }

    .cpbn-bot-msg.from-bot {
        background: var(--card, #ffffff);
        color: var(--text, #1a1a2e);
        border: 1px solid var(--border, #e5e7eb);
        border-bottom-left-radius: 4px;
    }

    .cpbn-bot-choices {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }

    .cpbn-bot-choices button {
        padding: 6px 11px;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 100px;
        background: var(--bg, #f4f6f9);
        color: var(--primary, #1a3a5c);
        font-family: inherit;
        font-size: 12px;
        cursor: pointer;
    }

    .cpbn-bot-choices button:hover,
    .cpbn-bot-choices button.is-picked {
        border-color: var(--primary, #1a3a5c);
        background: var(--primary, #1a3a5c);
        color: #ffffff;
    }

    .cpbn-bot-choices .cpbn-bot-choices-send {
        border-color: var(--accent, #c9a84c);
        background: var(--accent, #c9a84c);
        color: #ffffff;
        font-weight: 600;
    }

    .cpbn-bot-choices [hidden] {
        display: none;
    }

    /* Fields the bot has just filled in, so the student can check them. */
    .cpbn-bot-filled {
        outline: 3px solid var(--accent, #c9a84c) !important;
        outline-offset: 2px;
        border-radius: 6px;
        transition: outline-color 0.4s ease;
    }

    .cpbn-bot-msg.is-filled-note {
        max-width: none;
        margin: -4px 0 10px;
        padding: 0 4px;
        border: none;
        background: none;
        color: var(--text-muted, #6b7280);
        font-size: 11px;
    }

    .cpbn-bot-msg.is-going {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
        font-weight: 600;
    }

    .cpbn-bot-stay {
        flex-shrink: 0;
        padding: 4px 10px;
        border: 1px solid #86efac;
        border-radius: 100px;
        background: #ffffff;
        color: #166534;
        font-family: inherit;
        font-size: 12px;
        cursor: pointer;
    }

    .cpbn-bot-msg.is-error {
        border-color: #fca5a5;
        background: #fef2f2;
        color: #9b2c2c;
    }

    .cpbn-bot-msg p { margin: 0 0 8px; }
    .cpbn-bot-msg p:last-child { margin-bottom: 0; }
    .cpbn-bot-msg .adviser-list { margin: 4px 0 8px; padding-left: 18px; }
    .cpbn-bot-msg .adviser-list li { margin-bottom: 4px; }
    .cpbn-bot-msg .adviser-heading { margin: 8px 0 4px; font-size: 13px; }

    .cpbn-bot-typing span {
        display: inline-block;
        width: 6px;
        height: 6px;
        margin-right: 3px;
        border-radius: 50%;
        background: var(--text-muted, #6b7280);
        animation: cpbnBotDot 1s infinite ease-in-out;
    }

    .cpbn-bot-typing span:nth-child(2) { animation-delay: 0.15s; }
    .cpbn-bot-typing span:nth-child(3) { animation-delay: 0.3s; }

    @keyframes cpbnBotDot {
        0%, 80%, 100% { opacity: 0.3; }
        40% { opacity: 1; }
    }

    .cpbn-bot-empty p {
        margin: 0 0 12px;
        font-size: 13px;
        line-height: 1.6;
        color: var(--text, #1a1a2e);
    }

    .cpbn-bot-chips {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .cpbn-bot-chips button {
        padding: 7px 12px;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 100px;
        background: var(--card, #ffffff);
        color: var(--primary, #1a3a5c);
        font-family: inherit;
        font-size: 12px;
        cursor: pointer;
    }

    .cpbn-bot-chips button:hover {
        border-color: var(--primary, #1a3a5c);
    }

    .cpbn-bot-composer {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        margin: 0;
        padding: 10px;
        border-top: 1px solid var(--border, #e5e7eb);
        background: var(--card, #ffffff);
    }

    .cpbn-bot-composer textarea {
        flex: 1;
        max-height: 110px;
        padding: 9px 11px;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 10px;
        font-family: inherit;
        font-size: 13px;
        line-height: 1.4;
        resize: none;
    }

    .cpbn-bot-composer textarea:focus {
        outline: none;
        border-color: var(--primary, #1a3a5c);
    }

    .cpbn-bot-composer button {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border: none;
        border-radius: 10px;
        background: var(--primary, #1a3a5c);
        color: #ffffff;
        cursor: pointer;
    }

    .cpbn-bot-composer button:disabled,
    .cpbn-bot-composer textarea:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .cpbn-bot-fab {
            right: 14px;
            bottom: 14px;
            width: 60px;
            height: 60px;
        }

        .cpbn-bot {
            right: 12px;
            left: 12px;
            bottom: 84px;
            width: auto;
            height: min(560px, calc(100vh - 104px));
        }
    }
</style>

<script>
(function () {
    const panel = document.getElementById('cpbnBot');
    const toggle = document.getElementById('cpbnBotToggle');

    if (! panel || ! toggle) {
        return;
    }

    const el = (id) => document.getElementById(id);
    const messages = el('cpbnBotMessages');
    const empty = el('cpbnBotEmpty');
    const list = el('cpbnBotList');
    const listItems = el('cpbnBotListItems');
    const historyButton = el('cpbnBotHistory');
    const form = el('cpbnBotForm');
    const input = el('cpbnBotInput');
    const send = el('cpbnBotSend');
    const ring = el('cpbnBotRing');
    const ringFill = el('cpbnBotRingFill');
    const usage = el('cpbnBotUsage');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    /* Kept per browser tab, and per account so a shared computer
     * never shows one student another's session. */
    const storeKey = 'cpbnBot:' + panel.dataset.user;
    const RING_LENGTH = 97.4;

    const state = {
        loaded: false,
        busy: false,
        available: true,
        historyEnabled: false,
        conversationId: null,
        newConversation: false,
        fillSection: null,
    };

    function remember(patch) {
        try {
            const saved = JSON.parse(sessionStorage.getItem(storeKey) || '{}');
            sessionStorage.setItem(storeKey, JSON.stringify(Object.assign(saved, patch)));
        } catch (error) {
            /* Storage can be unavailable; the panel works without it. */
        }
    }

    function recall() {
        try {
            return JSON.parse(sessionStorage.getItem(storeKey) || '{}');
        } catch (error) {
            return {};
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;

        return div.innerHTML.replace(/\n/g, '<br>');
    }

    function addMessage(role, html, extraClass) {
        empty.hidden = true;

        const bubble = document.createElement('div');
        bubble.className = 'cpbn-bot-msg ' + (role === 'user' ? 'from-student' : 'from-bot') + (extraClass ? ' ' + extraClass : '');
        bubble.innerHTML = html;
        messages.appendChild(bubble);
        messages.scrollTop = messages.scrollHeight;

        return bubble;
    }

    /* Buttons to the pages a reply mentions. */
    function linksHtml(links) {
        if (! links || links.length === 0) {
            return '';
        }

        return '<div class="cpbn-bot-links">' + links.map((link) => {
            const anchor = document.createElement('a');
            anchor.href = link.url;
            anchor.textContent = link.label + ' →';

            return anchor.outerHTML;
        }).join('') + '</div>';
    }

    function clearMessages() {
        messages.querySelectorAll('.cpbn-bot-msg').forEach((node) => node.remove());
        empty.hidden = false;
    }

    function saveSessionMessages() {
        if (state.historyEnabled) {
            return;
        }

        const saved = [];

        messages.querySelectorAll('.cpbn-bot-msg:not(.is-error):not(.cpbn-bot-typing):not(.is-going):not(.is-filled-note)').forEach((node) => {
            saved.push({
                role: node.classList.contains('from-student') ? 'user' : 'assistant',
                html: node.innerHTML,
            });
        });

        remember({ messages: saved.slice(-30) });
    }

    function renderConversation(conversation) {
        clearMessages();

        (conversation?.messages || []).forEach((message) => addMessage(message.role, message.html + linksHtml(message.links)));
    }

    function formatNumber(value) {
        return Number(value || 0).toLocaleString('en-GB');
    }

    function renderTokens(tokens) {
        if (! tokens) {
            return;
        }

        ring.classList.remove('is-warning', 'is-danger', 'is-unlimited');

        if (tokens.mode === 'unlimited') {
            ring.classList.add('is-unlimited');
            ringFill.style.strokeDashoffset = 0;
            usage.textContent = formatNumber(tokens.used) + ' tokens used · no limit';
            ring.title = 'Your plan has no token limit.';

            return;
        }

        if (tokens.limit === null) {
            ringFill.style.strokeDashoffset = RING_LENGTH;
            usage.textContent = 'Unavailable';

            return;
        }

        const percent = Math.min(100, tokens.percent_used || 0);

        ringFill.style.strokeDashoffset = RING_LENGTH * (1 - percent / 100);

        if (percent >= 90) {
            ring.classList.add('is-danger');
        } else if (percent >= 70) {
            ring.classList.add('is-warning');
        }

        const period = tokens.period ? ' in ' + tokens.period : '';
        const summary = percent + '% · ' + formatNumber(tokens.used) + ' of ' + formatNumber(tokens.limit) + ' tokens used' + period;

        usage.textContent = tokens.allowed
            ? summary
            : (tokens.next_available_label ? 'Limit reached · back ' + tokens.next_available_label : 'Limit reached');

        ring.title = summary + (tokens.allowed ? '' : '. Limit reached.');
    }

    function setBusy(busy) {
        state.busy = busy;
        send.disabled = busy || ! state.available;
        input.disabled = ! state.available;
    }

    async function getJson(url) {
        const response = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });

        if (! response.ok) {
            throw new Error('Request failed');
        }

        return response.json();
    }

    async function load() {
        if (state.loaded) {
            return;
        }

        state.loaded = true;

        try {
            const data = await getJson(panel.dataset.panelUrl);

            state.available = data.available;
            state.historyEnabled = data.history_enabled;
            historyButton.hidden = ! data.history_enabled;
            renderTokens(data.tokens);

            if (! data.available) {
                addMessage('assistant', escapeHtml(data.unavailable_message || 'The CPBN Bot is unavailable at the moment.'), 'is-error');
            }

            const saved = recall();

            if (data.history_enabled) {
                if (saved.newConversation) {
                    state.newConversation = true;
                } else if (data.conversation) {
                    const reopened = saved.conversationId && saved.conversationId !== data.conversation.id
                        ? await getJson(panel.dataset.conversationsUrl + '/' + saved.conversationId).then((json) => json.conversation).catch(() => null)
                        : null;

                    const shown = reopened || data.conversation;

                    state.conversationId = shown.id;
                    renderConversation(shown);
                }
            } else {
                (saved.messages || []).forEach((message) => addMessage(message.role, message.html));

                /* Without history, each browser session starts its own
                 * conversation, so the bot never carries on from one
                 * the student can no longer see. */
                state.newConversation = !! saved.newConversation || ! (saved.messages || []).length;
            }
        } catch (error) {
            state.loaded = false;
            usage.textContent = 'Could not load';
        }

        setBusy(false);
    }

    function open() {
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        remember({ open: true });
        load().then(() => input.focus());
    }

    function close() {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        remember({ open: false });
        toggle.focus();
    }

    function startNewConversation() {
        state.conversationId = null;
        state.newConversation = true;
        list.hidden = true;
        messages.hidden = false;
        historyButton.classList.remove('is-active');
        clearMessages();
        remember({ conversationId: null, newConversation: true, messages: [] });
        input.focus();
    }

    async function showHistory() {
        if (! list.hidden) {
            list.hidden = true;
            messages.hidden = false;
            historyButton.classList.remove('is-active');

            return;
        }

        list.hidden = false;
        messages.hidden = true;
        historyButton.classList.add('is-active');
        listItems.innerHTML = '<p class="cpbn-bot-list-title" style="text-transform:none;">Loading…</p>';

        try {
            const data = await getJson(panel.dataset.conversationsUrl);

            listItems.innerHTML = '';

            if (data.conversations.length === 0) {
                listItems.innerHTML = '<p class="cpbn-bot-list-title" style="text-transform:none;">No past conversations yet.</p>';

                return;
            }

            data.conversations.forEach((conversation) => {
                const row = document.createElement('div');
                row.className = 'cpbn-bot-list-row';

                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'cpbn-bot-list-item' + (conversation.id === state.conversationId ? ' is-current' : '');
                item.innerHTML = '<strong></strong><span></span>';
                item.querySelector('strong').textContent = conversation.title;
                item.querySelector('span').textContent = (conversation.last_message_label || '') + ' · ' + conversation.message_count + ' messages';
                item.addEventListener('click', () => reopen(conversation.id));

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'cpbn-bot-list-delete';
                remove.title = 'Delete this conversation';
                remove.setAttribute('aria-label', 'Delete conversation: ' + conversation.title);
                remove.innerHTML = '<i class="fas fa-trash-can"></i>';
                remove.addEventListener('click', () => confirmDelete(conversation, row));

                row.append(item, remove);
                listItems.appendChild(row);
            });
        } catch (error) {
            listItems.innerHTML = '<p class="cpbn-bot-list-title" style="text-transform:none;">Could not load past conversations.</p>';
        }
    }

    /*
     * Takes the student where they asked to go. A moment's pause,
     * with a way to stay, so a misread request costs nothing. The
     * panel stays open on the next page, and the conversation
     * carries on there.
     *
     * Never over unsaved work: if a form on the page has been
     * changed, the student is asked first, exactly as when they
     * click a link away from it.
     */
    function goTo(place) {
        const target = new URL(place.url, window.location.origin);

        if (target.pathname + target.search === window.location.pathname + window.location.search) {
            return;
        }

        if (hasUnsavedChanges()) {
            leaveWithConfirmation(place, target);

            return;
        }

        const note = addMessage(
            'assistant',
            '<span>Opening ' + escapeHtml(place.label) + '…</span> <button type="button" class="cpbn-bot-stay">Stay here</button>',
            'is-going'
        );

        const timer = setTimeout(() => {
            /* Checked again: the student may have typed meanwhile. */
            if (hasUnsavedChanges()) {
                note.remove();
                leaveWithConfirmation(place, target);

                return;
            }

            remember({ open: true });
            window.location.href = target.href;
        }, 1200);

        note.querySelector('.cpbn-bot-stay').addEventListener('click', () => {
            clearTimeout(timer);
            note.remove();
            input.focus();
        });
    }

    /*
     * The profile form keeps its own record of whether it has
     * changed, and its own warning on leaving. Any other form is
     * compared with how the page first drew it.
     */
    function hasUnsavedChanges() {
        if (window.formChanged === true) {
            return true;
        }

        return Array.from(document.querySelectorAll('form')).some((form) => {
            if (form.id === 'profile-form'
                || form.closest('#cpbnBot')
                || form.hasAttribute('data-unsaved-ignore')
                || (form.getAttribute('method') || 'get').toLowerCase() === 'get') {
                return false;
            }

            return Array.from(form.elements).some(fieldChanged);
        });
    }

    function fieldChanged(field) {
        const type = (field.type || '').toLowerCase();

        if (field.disabled || ['hidden', 'submit', 'button', 'reset', 'image'].includes(type)) {
            return false;
        }

        if (type === 'checkbox' || type === 'radio') {
            return field.checked !== field.defaultChecked;
        }

        if (type === 'file') {
            return field.files && field.files.length > 0;
        }

        if (field.tagName === 'SELECT') {
            const options = Array.from(field.options);

            if (field.multiple) {
                return options.some((option) => option.selected !== option.defaultSelected);
            }

            /* With nothing marked, a list starts on its first option. */
            const initial = Math.max(0, options.findIndex((option) => option.defaultSelected));

            return options.length > 0 && field.selectedIndex !== initial;
        }

        if (field.tagName === 'TEXTAREA' || field.tagName === 'INPUT') {
            return field.value !== field.defaultValue;
        }

        return false;
    }

    function leaveWithConfirmation(place, target) {
        const note = addMessage(
            'assistant',
            '<span>You have unsaved changes here, so I\'ve asked before opening ' + escapeHtml(place.label) + '.</span>',
            'is-going'
        );

        const leave = () => {
            remember({ open: true });
            window.location.href = target.href;
        };

        /*
         * On the profile form, its own warning handles leaving, so
         * the student is asked once, the way they always are there.
         */
        if (document.getElementById('profile-form') && window.formChanged === true) {
            const link = document.createElement('a');
            link.href = target.href;
            link.hidden = true;
            document.body.appendChild(link);
            remember({ open: true });
            link.click();
            link.remove();

            return;
        }

        if (typeof window.showConfirmModal === 'function') {
            window.showConfirmModal({
                title: 'Unsaved Changes',
                message: 'You have unsaved changes on this page. If you leave it, those changes will be lost.',
                confirmText: 'Leave Page',
                cancelText: 'Stay Here',
                type: 'warning',
                onConfirm: leave,
                onCancel: () => note.remove(),
            });
        } else if (window.confirm('You have unsaved changes on this page. Leave anyway?')) {
            leave();
        } else {
            note.remove();
        }
    }

    function confirmDelete(conversation, row) {
        const go = () => deleteConversation(conversation.id, row);

        if (typeof window.showConfirmModal === 'function') {
            window.showConfirmModal({
                title: 'Delete conversation',
                message: 'Delete "' + conversation.title + '"? It cannot be recovered.',
                confirmText: 'Delete',
                cancelText: 'Keep',
                type: 'danger',
                onConfirm: go,
            });
        } else if (window.confirm('Delete this conversation? It cannot be recovered.')) {
            go();
        }
    }

    async function deleteConversation(id, row) {
        try {
            const response = await fetch(panel.dataset.conversationsUrl + '/' + id, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (! response.ok) {
                throw new Error('Delete failed');
            }

            row.remove();

            /* The one on screen is gone, so start afresh. */
            if (id === state.conversationId) {
                state.conversationId = null;
                state.newConversation = true;
                clearMessages();
                remember({ conversationId: null, newConversation: true });
            }

            if (! listItems.querySelector('.cpbn-bot-list-row')) {
                listItems.innerHTML = '<p class="cpbn-bot-list-title" style="text-transform:none;">No past conversations yet.</p>';
            }
        } catch (error) {
            row.querySelector('.cpbn-bot-list-delete')?.classList.add('is-failed');
        }
    }

    async function reopen(id) {
        try {
            const data = await getJson(panel.dataset.conversationsUrl + '/' + id);

            state.conversationId = data.conversation.id;
            state.newConversation = false;
            remember({ conversationId: state.conversationId, newConversation: false });
            renderConversation(data.conversation);
        } catch (error) {
            addMessage('assistant', 'That conversation could not be opened.', 'is-error');
        }

        list.hidden = true;
        messages.hidden = false;
        historyButton.classList.remove('is-active');
    }

    /*
     * Answers to click instead of type. One click sends a single
     * answer; with several allowed, they are ticked and sent
     * together. Older choices stop working once anything is sent.
     */
    function showChoices(bubble, choices) {
        const box = document.createElement('div');
        box.className = 'cpbn-bot-choices';

        const picked = new Set();

        choices.options.forEach((option) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = option;
            button.addEventListener('click', () => {
                if (! choices.multiple) {
                    ask(option);

                    return;
                }

                picked.has(option) ? picked.delete(option) : picked.add(option);
                button.classList.toggle('is-picked', picked.has(option));
                button.setAttribute('aria-pressed', picked.has(option) ? 'true' : 'false');
                send.hidden = picked.size === 0;
            });
            box.appendChild(button);
        });

        const send = document.createElement('button');
        send.type = 'button';
        send.className = 'cpbn-bot-choices-send';
        send.textContent = 'Send';
        send.hidden = true;
        send.addEventListener('click', () => ask(Array.from(picked).join(', ')));

        if (choices.multiple) {
            box.appendChild(send);
        }

        bubble.appendChild(box);
        messages.scrollTop = messages.scrollHeight;
    }

    /*
     * Writes what the bot collected into the Edit Profile form. It
     * only ticks, chooses and adds: nothing is unticked or removed,
     * and nothing is saved. The student checks the form and presses
     * Save Changes, so the form's own rules and warnings still apply.
     */
    function applyFill(fill) {
        const form = document.getElementById('profile-form');

        if (! form) {
            return;
        }

        const changed = [];
        const done = [];

        const set = (field, value) => {
            if (! field || value === undefined || value === null || value === '') {
                return false;
            }

            /* Already says this: nothing to change or highlight. */
            if (field.value === String(value)) {
                return false;
            }

            field.value = value;
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
            changed.push(field);

            return true;
        };

        const tick = (box) => {
            if (box && ! box.checked) {
                box.checked = true;
                box.dispatchEvent(new Event('change', { bubbles: true }));
            }

            if (box) {
                changed.push(box.closest('label') || box);
            }
        };

        (fill.competencies || []).forEach((item) => {
            const box = form.querySelector('input[name="biicf_competencies[' + item.id + '][selected]"]');
            const level = form.querySelector('select[name="biicf_competencies[' + item.id + '][proficiency_level_id]"]');

            if (! box) {
                return;
            }

            tick(box);

            if (level && item.level_id) {
                level.disabled = false;
                set(level, String(item.level_id));
            }

            changed.push(box.closest('[data-biicf-competency]') || box);
        });

        if ((fill.competencies || []).length) {
            done.push('Skills & Competencies');
        }

        (fill.interests || []).forEach((item) => {
            if (item.listed) {
                const box = Array.from(form.querySelectorAll('input[name="interests[]"]')).find((input) => input.value === item.name);
                tick(box);

                return;
            }

            const toggle = document.getElementById('additional-interests-toggle');
            const input = document.getElementById('additional-interest-input');
            const add = document.getElementById('add-additional-interest');

            if (! input || ! add) {
                return;
            }

            if (toggle && ! toggle.checked) {
                tick(toggle);
            }

            input.value = item.name;
            add.click();
            changed.push(input);
        });

        if ((fill.interests || []).length) {
            done.push('Interests & Preferences');
        }

        const aspirations = fill.aspirations || {};
        const aspirationFields = {
            dream_career: 'career_goals_text',
            vision_statement: 'vision_statement',
            long_term_goals: 'long_term_goals',
        };

        if (Object.keys(aspirationFields).some((key) => set(form.querySelector('[name="' + aspirationFields[key] + '"]'), aspirations[key]))) {
            done.push('Career Aspirations');
        }

        const personal = fill.personal || {};
        let personalDone = set(form.querySelector('[name="date_of_birth"]'), personal.date_of_birth);

        if (personal.phone) {
            const phone = form.querySelector('#phone');
            personalDone = set(phone, personal.phone) || personalDone;
            phone?.dispatchEvent(new Event('blur'));
        }

        if (personal.nationality) {
            const select = form.querySelector('select[name="nationality"]');
            const wanted = personal.nationality.toLowerCase();
            const option = select
                ? Array.from(select.options).find((o) => o.value.toLowerCase() === wanted || o.text.trim().toLowerCase() === wanted)
                : null;

            if (option) {
                personalDone = set(select, option.value) || personalDone;
            }
        }

        if (personalDone) {
            done.push('Personal Information');
        }

        const addRows = (rows, listId, buttonId, label) => {
            const list = document.getElementById(listId);
            const button = document.getElementById(buttonId);

            if (! rows || ! rows.length || ! list || ! button) {
                return;
            }

            const key = {
                'project-list': 'title',
                'certification-list': 'certification_name',
                'qualification-list': 'programme_name',
            }[listId];

            rows.forEach((row) => {
                /*
                 * The bot sends the whole item every turn as it learns
                 * more, so an item already on the form, matched by its
                 * title, is updated rather than added again.
                 */
                const wanted = String(row[key] || '').trim().toLowerCase();

                let card = Array.from(list.children).find((existing) => {
                    const field = existing.querySelector('[name$="[' + key + ']"]');

                    return field && wanted !== '' && field.value.trim().toLowerCase() === wanted;
                });

                if (! card) {
                    button.click();
                    card = list.lastElementChild;
                }

                Object.entries(row).forEach(([field, value]) => {
                    if (field === 'is_current') {
                        /* Ticked only: never unticked. */
                        if (value === true) {
                            tick(card?.querySelector('input[type="checkbox"][name$="[is_current]"]'));
                        }

                        return;
                    }

                    set(card?.querySelector('[name$="[' + field + ']"]:not([type="hidden"])'), value);
                });

                if (card) {
                    changed.push(card);
                }
            });

            done.push(label);
        };

        addRows(fill.qualifications, 'qualification-list', 'add-qualification', 'Qualifications');
        addRows(fill.projects, 'project-list', 'add-project', 'Projects & Experience');
        addRows(fill.certifications, 'certification-list', 'add-certification', 'Certifications');

        if (typeof window.setFormChanged === 'function') {
            window.setFormChanged(true);
        }

        changed.filter(Boolean).forEach((element) => {
            element.classList.add('cpbn-bot-filled');
            setTimeout(() => element.classList.remove('cpbn-bot-filled'), 6000);
        });

        const first = changed.find((element) => element && element.offsetParent !== null);
        first?.scrollIntoView({ behavior: 'smooth', block: 'center' });

        /* A quiet note of what changed; the bot reminds them to save. */
        if (done.length && changed.length) {
            addMessage(
                'assistant',
                '<i class="fas fa-pen"></i> Updated on the form: ' + escapeHtml(done.join(', ')),
                'is-filled-note'
            );
        }
    }

    /*
     * Reads one section of the Edit Profile form as it stands, typed
     * changes included, so the bot works from what is on the page
     * rather than from what was said in the chat.
     */
    function readSection(section) {
        const form = document.getElementById('profile-form');

        if (! form) {
            return null;
        }

        const value = (name) => form.querySelector('[name="' + name + '"]')?.value || '';

        const cards = (listId, fields) => {
            const list = document.getElementById(listId);

            return list
                ? Array.from(list.children)
                    .filter((card) => card.querySelector('[name$="[' + fields[0] + ']"]'))
                    .map((card) => {
                        const item = {};

                        fields.forEach((field) => {
                            const input = field === 'is_current'
                                ? card.querySelector('input[type="checkbox"][name$="[is_current]"]')
                                : card.querySelector('[name$="[' + field + ']"]:not([type="hidden"])');

                            if (input) {
                                item[field] = input.type === 'checkbox' ? input.checked : input.value;
                            }
                        });

                        return item;
                    })
                : [];
        };

        switch (section) {
            case 'personal':
                return {
                    phone: form.querySelector('#phone')?.value || '',
                    date_of_birth: value('date_of_birth'),
                    nationality: value('nationality'),
                };
            case 'qualifications':
                return {
                    qualifications: cards('qualification-list', ['programme_name', 'institution_name', 'level', 'bdqf_level', 'field_of_study', 'start_date', 'end_date', 'is_current']),
                };
            case 'skills':
                return {
                    competencies: Array.from(form.querySelectorAll('[data-biicf-competency]'))
                        .filter((item) => item.querySelector('input[type="checkbox"]')?.checked)
                        .map((item) => ({
                            name: item.querySelector('.cpbn-competency-choice span')?.textContent.trim(),
                            level: item.querySelector('select')?.selectedOptions[0]?.textContent.replace(/\s+/g, ' ').trim() || '',
                        })),
                };
            case 'interests':
                return {
                    interests: Array.from(form.querySelectorAll('input[name="interests[]"]:checked')).map((input) => input.value)
                        .concat(Array.from(form.querySelectorAll('input[name="custom_interests[]"]')).map((input) => input.value)),
                };
            case 'projects':
                return {
                    projects: cards('project-list', ['title', 'role', 'project_url', 'description', 'technologies_used', 'start_date', 'end_date', 'achievements']),
                };
            case 'certifications':
                return {
                    certifications: cards('certification-list', ['certification_name', 'issuing_organization', 'issue_date']),
                };
            case 'aspirations':
                return {
                    dream_career: value('career_goals_text'),
                    vision_statement: value('vision_statement'),
                    long_term_goals: value('long_term_goals'),
                };
            default:
                return null;
        }
    }

    async function ask(question) {
        if (state.busy || ! state.available) {
            return;
        }

        /* Choices offered earlier no longer apply. */
        messages.querySelectorAll('.cpbn-bot-choices').forEach((box) => box.remove());

        question = question.trim();

        if (question === '') {
            return;
        }

        list.hidden = true;
        messages.hidden = false;
        historyButton.classList.remove('is-active');

        addMessage('user', escapeHtml(question));
        input.value = '';
        input.style.height = '';
        saveSessionMessages();

        const typing = addMessage('assistant', '<span></span><span></span><span></span>', 'cpbn-bot-typing');
        setBusy(true);

        const body = { message: question };

        if (panel.dataset.currentPage) {
            body.current_page = panel.dataset.currentPage;
        }

        if (state.newConversation) {
            body.new_conversation = true;
        } else if (state.conversationId) {
            body.conversation_id = state.conversationId;
        }

        /* What the form holds right now for the section being filled. */
        const section = state.fillSection || recall().fillSection;
        const formNow = section ? readSection(section) : null;

        if (formNow) {
            body.form_state = formNow;
        }

        try {
            const response = await fetch(panel.dataset.askUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify(body),
            });

            const data = await response.json().catch(() => ({}));

            typing.remove();

            if (data.tokens) {
                renderTokens(data.tokens);
            }

            if (! response.ok || data.status !== 'completed') {
                addMessage('assistant', escapeHtml(data.message || 'The CPBN Bot could not answer just now. Please try again.'), 'is-error');

                return;
            }

            const reply = addMessage('assistant', (data.message_html || escapeHtml(data.message || '')) + linksHtml(data.links));

            if (data.choices && data.choices.options && data.choices.options.length) {
                showChoices(reply, data.choices);
            }

            if (data.conversation_id) {
                state.conversationId = data.conversation_id;
            }

            state.newConversation = false;
            remember({ conversationId: state.conversationId, newConversation: false });
            saveSessionMessages();

            /* The section in progress, so its form is sent next time. */
            state.fillSection = data.fill_section || null;
            remember({ fillSection: state.fillSection });

            if (data.fill) {
                if (document.getElementById('profile-form')) {
                    applyFill(data.fill);
                } else {
                    /* Filled in once Edit Profile has opened. */
                    remember({ pendingFill: data.fill });
                }
            }

            if (data.navigate && data.navigate.url) {
                goTo(data.navigate);
            }
        } catch (error) {
            typing.remove();
            addMessage('assistant', 'The CPBN Bot could not be reached. Check your connection and try again.', 'is-error');
        } finally {
            setBusy(false);
            input.focus();
        }
    }

    toggle.addEventListener('click', () => (panel.hidden ? open() : close()));
    el('cpbnBotClose').addEventListener('click', close);
    el('cpbnBotNew').addEventListener('click', startNewConversation);
    historyButton.addEventListener('click', showHistory);

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        ask(input.value);
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && ! event.shiftKey) {
            event.preventDefault();
            ask(input.value);
        }
    });

    input.addEventListener('input', () => {
        input.style.height = '';
        input.style.height = Math.min(input.scrollHeight, 110) + 'px';
    });

    panel.addEventListener('click', (event) => {
        const chip = event.target.closest('[data-bot-suggest]');

        if (chip) {
            ask(chip.textContent);
        }
    });

    document.addEventListener('keydown', (event) => {
        const guide = document.getElementById('cpbnGuideOverlay');

        /* Escape closes a '?' guide first if one is open. */
        if (event.key === 'Escape' && ! panel.hidden && ! (guide && ! guide.hidden)) {
            close();
        }
    });

    /*
     * Lets the rest of the page hand the bot a question, as the '?'
     * guides on Edit Profile do with "Help me fill this in".
     */
    window.cpbnBotAsk = async function (text, section) {
        open();
        await load();

        if (section) {
            state.fillSection = section;
        }

        ask(text);
    };

    document.body.classList.add('has-cpbn-bot');

    /* A fill asked for on another page, now that Edit Profile is open. */
    const pendingFill = recall().pendingFill;

    if (pendingFill && document.getElementById('profile-form')) {
        remember({ pendingFill: null });

        /* After the form's own scripts have set up its baseline. */
        window.addEventListener('load', () => setTimeout(() => applyFill(pendingFill), 300));
    }

    /* Stay open across pages if it was open. */
    if (recall().open) {
        open();
    }
})();
</script>
