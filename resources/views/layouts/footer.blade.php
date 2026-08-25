<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="logo-text">CareerPath <span>BN</span></div>
                <p>AI-powered career guidance platform aligned with the Brunei ICT Industry Competency Framework (BIICF).</p>
                <div class="footer-logos">
                    <a href="https://www.pb.edu.bn" target="_blank" class="logo-link">
                        @if(file_exists(public_path('images/politeknik-logo.png')))
                            <img src="{{ asset('images/politeknik-logo.png') }}" alt="Politeknik Brunei" class="footer-logo">
                        @else
                            <span class="logo-fallback"><i class="fas fa-university"></i> Politeknik Brunei</span>
                        @endif
                    </a>
                    <a href="https://www.biicf.bn" target="_blank" class="logo-link">
                        @if(file_exists(public_path('images/biicf-logo.png')))
                            <img src="{{ asset('images/biicf-logo.png') }}" alt="BIICF" class="footer-logo">
                        @else
                            <span class="logo-fallback"><i class="fas fa-certificate"></i> BIICF</span>
                        @endif
                    </a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <a href="{{ route('student.dashboard') }}">Dashboard</a>
                <a href="{{ route('student.profile') }}">Profile</a>
                <a href="{{ route('student.milestones') }}">Milestones</a>
                <a href="{{ route('student.biicf-explorer.index') }}">BIICF Explorer</a>
            </div>
            <div class="footer-col">
                <h4>Resources</h4>
                <a href="{{ route('student.biicf-explorer.index') }}">BIICF Framework</a>
                <a href="{{ route('student.biicf-explorer.index') }}">ICT Sub-Sectors</a>
                <a href="{{ route('student.biicf-explorer.index') }}">Competencies</a>
                <a href="#">Career Guide</a>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <a href="#"><i class="fas fa-university"></i> Politeknik Brunei</a>
                <a href="#"><i class="fas fa-map-marker-alt"></i> Jalan Ong Sum Ping, BSB</a>
                <a href="#"><i class="fas fa-envelope"></i> sict@pb.edu.bn</a>
                <a href="#"><i class="fas fa-phone"></i> +673 123 4567</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} CareerPath BN. Developed by SICT Students, Politeknik Brunei.</p>
            <p class="credit">In collaboration with AITI - Brunei ICT Industry Competency Framework (BIICF)</p>
        </div>
    </div>
</footer>

<style>
    .site-footer {
        background: var(--primary-dark);
        color: white;
        padding: 48px 0 24px;
        margin-top: 40px;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 40px;
        margin-bottom: 32px;
    }

    .footer-brand .logo-text {
        font-family: 'Playfair Display', serif;
        font-size: 22px;
        font-weight: 700;
        color: white;
    }

    .footer-brand .logo-text span {
        color: var(--accent);
    }

    .footer-brand p {
        color: rgba(255, 255, 255, 0.5);
        font-size: 14px;
        max-width: 300px;
        margin-top: 12px;
        line-height: 1.7;
    }

    .footer-logos {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .footer-logos .logo-link {
        display: inline-block;
        transition: var(--transition);
    }

    .footer-logos .logo-link:hover {
        transform: translateY(-2px);
        opacity: 0.8;
    }

    .footer-logos .footer-logo {
        height: 40px;
        width: auto;
        object-fit: contain;
        filter: brightness(0) invert(1) opacity(0.8);
        transition: var(--transition);
    }

    .footer-logos .footer-logo:hover {
        filter: brightness(0) invert(1) opacity(1);
    }

    .footer-logos .logo-fallback {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.06);
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: rgba(255, 255, 255, 0.7);
        font-size: 13px;
        font-weight: 500;
        transition: var(--transition);
    }

    .footer-logos .logo-fallback:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .footer-logos .logo-fallback i {
        color: var(--accent);
    }

    .footer-col h4 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 16px;
        color: white;
    }

    .footer-col a {
        display: block;
        color: rgba(255, 255, 255, 0.5);
        text-decoration: none;
        font-size: 13px;
        padding: 4px 0;
        transition: var(--transition);
    }

    .footer-col a:hover {
        color: var(--accent);
    }

    .footer-col a i {
        width: 20px;
        margin-right: 4px;
    }

    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding-top: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .footer-bottom p {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.3);
        margin: 0;
    }

    .footer-bottom .credit {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.2);
    }

    .footer-social {
        display: flex;
        gap: 16px;
    }

    .footer-social a {
        color: rgba(255, 255, 255, 0.3);
        font-size: 18px;
        transition: var(--transition);
    }

    .footer-social a:hover {
        color: var(--accent);
        transform: translateY(-2px);
    }

    @media (max-width: 1024px) {
        .footer-grid {
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
    }

    @media (max-width: 768px) {
        .footer-grid {
            grid-template-columns: 1fr;
            gap: 24px;
        }
        .footer-bottom {
            flex-direction: column;
            text-align: center;
        }
        .footer-brand p {
            max-width: 100%;
        }
        .footer-logos {
            justify-content: flex-start;
        }
    }

    @media (max-width: 480px) {
        .footer-logos .footer-logo {
            height: 32px;
        }
        .footer-logos {
            gap: 12px;
        }
    }
</style>