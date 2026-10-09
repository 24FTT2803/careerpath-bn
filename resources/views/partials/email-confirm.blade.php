{{--
    Explains which footer email is which before the email app opens:
    one is Politeknik Brunei's, the other is the CareerPath BN team's.
    Self-contained, so it works on the homepage and every other page.
--}}
@once
<style>
    .email-confirm-overlay {
        position: fixed;
        inset: 0;
        z-index: 10060;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        background: rgba(13, 31, 51, 0.55);
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .email-confirm-overlay.is-open {
        opacity: 1;
    }

    .email-confirm {
        width: 100%;
        max-width: 420px;
        overflow: hidden;
        border-radius: 14px;
        background: #fff;
        color: #1a1a2e;
        font-family: inherit;
        box-shadow: 0 24px 60px -20px rgba(13, 31, 51, 0.55);
        transform: translateY(12px) scale(0.97);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .email-confirm-overlay.is-open .email-confirm {
        transform: none;
    }

    .email-confirm-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 22px 0;
    }

    .email-confirm-icon {
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: rgba(26, 58, 92, 0.08);
        color: #1a3a5c;
    }

    .email-confirm-icon.is-gold {
        background: rgba(201, 168, 76, 0.16);
        color: #8a6d22;
    }

    .email-confirm-head h3 {
        margin: 0;
        font-size: 17px;
        font-weight: 700;
        color: #0d1f33;
    }

    .email-confirm-head small {
        display: block;
        margin-top: 2px;
        font-size: 12px;
        color: #6b7280;
    }

    .email-confirm-body {
        padding: 14px 22px 4px;
        font-size: 14px;
        line-height: 1.6;
        color: #374151;
    }

    .email-confirm-address {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin: 14px 22px 0;
        padding: 10px 12px;
        border-radius: 8px;
        background: #f4f6f9;
        border: 1px solid #e5e7eb;
        font-size: 14px;
        font-weight: 600;
        color: #1a3a5c;
        overflow-wrap: anywhere;
    }

    .email-confirm-copy {
        flex-shrink: 0;
        border: 0;
        background: transparent;
        color: #8a6d22;
        font-family: inherit;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .email-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 18px 22px 20px;
    }

    .email-confirm-actions button,
    .email-confirm-actions a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 8px;
        border: 0;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }

    .email-confirm-cancel {
        background: #f1f2f4;
        color: #374151;
    }

    .email-confirm-go {
        background: #1a3a5c;
        color: #fff;
    }

    .email-confirm-go:hover {
        background: #2a5a8c;
    }

    @media (prefers-reduced-motion: reduce) {
        .email-confirm {
            transform: none !important;
        }
    }
</style>

<script>
    (function () {
        var overlay = null;

        function close() {
            if (! overlay) {
                return;
            }

            var current = overlay;
            overlay = null;
            current.classList.remove('is-open');
            setTimeout(function () {
                current.remove();
            }, 200);
            document.removeEventListener('keydown', onKey);
        }

        function onKey(event) {
            if (event.key === 'Escape') {
                close();
            }
        }

        function text(tag, className, value) {
            var element = document.createElement(tag);

            if (className) {
                element.className = className;
            }

            if (value) {
                element.textContent = value;
            }

            return element;
        }

        function open(link) {
            var address = link.getAttribute('href').replace(/^mailto:/i, '');

            overlay = text('div', 'email-confirm-overlay');
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-modal', 'true');

            var box = text('div', 'email-confirm');
            var head = text('div', 'email-confirm-head');
            var icon = text('div', 'email-confirm-icon' + (link.dataset.emailTone === 'gold' ? ' is-gold' : ''));
            icon.innerHTML = '<i class="fas ' + (link.dataset.emailIcon || 'fa-envelope') + '"></i>';

            var titles = text('div');
            titles.appendChild(text('h3', '', link.dataset.emailTitle || 'Send an email'));
            titles.appendChild(text('small', '', link.dataset.emailOwner || ''));
            head.appendChild(icon);
            head.appendChild(titles);

            var body = text('div', 'email-confirm-body', link.dataset.emailConfirm);

            var addressRow = text('div', 'email-confirm-address');
            addressRow.appendChild(text('span', '', address));

            var copy = text('button', 'email-confirm-copy', 'Copy');
            copy.type = 'button';
            copy.addEventListener('click', function () {
                var done = function () {
                    copy.textContent = 'Copied';
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(address).then(done, function () {});
                }
            });
            addressRow.appendChild(copy);

            var actions = text('div', 'email-confirm-actions');
            var cancel = text('button', 'email-confirm-cancel', 'Cancel');
            cancel.type = 'button';
            cancel.addEventListener('click', close);

            var go = text('a', 'email-confirm-go', 'Open email app');
            go.href = link.getAttribute('href');
            go.addEventListener('click', function () {
                setTimeout(close, 150);
            });

            actions.appendChild(cancel);
            actions.appendChild(go);

            box.appendChild(head);
            box.appendChild(body);
            box.appendChild(addressRow);
            box.appendChild(actions);
            overlay.appendChild(box);

            overlay.addEventListener('click', function (event) {
                if (event.target === overlay) {
                    close();
                }
            });

            document.body.appendChild(overlay);
            document.addEventListener('keydown', onKey);

            requestAnimationFrame(function () {
                overlay && overlay.classList.add('is-open');
                go.focus();
            });
        }

        document.addEventListener('click', function (event) {
            var link = event.target.closest && event.target.closest('a[data-email-confirm]');

            if (! link || event.defaultPrevented || event.metaKey || event.ctrlKey) {
                return;
            }

            event.preventDefault();
            open(link);
        });
    })();
</script>
@endonce
