{{--
    Picture panel beside the sign-in form (decoration only).
    Each page passes its own scene, so every page looks different:
    login, register, forgot, reset or verify.
--}}
@php
    $authScene = in_array($scene ?? null, ['login', 'register', 'forgot', 'reset', 'verify'], true)
        ? $scene
        : 'login';

    /* Use the photo when it is there, otherwise the drawn picture. */
    $authImage = file_exists(public_path('images/auth/'.$authScene.'.jpg'))
        ? $authScene.'.jpg'
        : $authScene.'.svg';
@endphp
<aside class="auth-visual auth-visual--{{ $authScene }}" id="authVisual" aria-hidden="true">
    <div class="auth-visual-media">
        <div
            class="auth-visual-photo"
            id="authVisualPhoto"
            style="--auth-scene: url('{{ asset('images/auth/'.$authImage) }}');"
        ></div>
        <div class="auth-visual-glow"></div>
        <div class="auth-visual-grid"></div>
    </div>

    <span class="auth-seam"><i class="fas fa-compass"></i></span>

    <div class="auth-visual-inner">
        <div class="auth-tile-wrap">
            <div class="auth-tile" id="authTile">
                <div class="auth-tile-badge">
                    @if(file_exists(public_path('images/careerpath-badge.png')))
                        <img src="{{ asset('images/careerpath-badge.png') }}" alt="">
                    @endif
                </div>
                <span class="auth-tile-shine"></span>
            </div>
        </div>

        <p class="auth-visual-caption">
            <strong>CareerPath <span>BN</span></strong>
            AI-powered career guidance aligned with the Brunei ICT Industry Competency Framework (BIICF).
        </p>
    </div>
</aside>

<script>
    (function () {
        /* The tile tilts and the photo drifts a little with the mouse. */
        var panel = document.getElementById('authVisual');
        var tile = document.getElementById('authTile');
        var photo = document.getElementById('authVisualPhoto');

        if (! panel || ! tile || ! window.matchMedia) {
            return;
        }

        if (! window.matchMedia('(hover: hover) and (pointer: fine)').matches
            || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        var frame = null;

        panel.addEventListener('pointermove', function (event) {
            var box = panel.getBoundingClientRect();
            var x = (event.clientX - box.left) / box.width - 0.5;
            var y = (event.clientY - box.top) / box.height - 0.5;

            if (frame) {
                cancelAnimationFrame(frame);
            }

            frame = requestAnimationFrame(function () {
                tile.style.transform = 'rotateY(' + (x * 16).toFixed(2) + 'deg) rotateX(' + (-y * 14).toFixed(2) + 'deg)';
                photo.style.translate = (x * -18).toFixed(1) + 'px ' + (y * -18).toFixed(1) + 'px';
            });
        });

        panel.addEventListener('pointerleave', function () {
            if (frame) {
                cancelAnimationFrame(frame);
            }

            tile.style.transform = '';
            photo.style.translate = '';
        });
    })();
</script>
