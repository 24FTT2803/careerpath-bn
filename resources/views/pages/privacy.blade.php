@extends('layouts.guest')

@section('title', 'Privacy Policy')
@section('eyebrow', 'Your data')
@section('icon', 'fa-shield-halved')
@section('updated', '10 October 2026')
@section('lead', 'What CareerPath BN collects about you, why we need it, who can see it, and how you stay in control of it.')

@section('summary')
    <div class="legal-point">
        <i class="fas fa-user-lock"></i>
        <div><strong>Only what the service needs</strong>Your profile is used to match you to BIICF careers. We never sell it.</div>
    </div>
    <div class="legal-point">
        <i class="fas fa-robot"></i>
        <div><strong>AI sees no identity</strong>Your name, email, Student ID, phone and files are never sent to the AI.</div>
    </div>
    <div class="legal-point">
        <i class="fas fa-chalkboard-user"></i>
        <div><strong>Staff see progress, not chats</strong>Lecturers see only their own classes. Nobody else can read your Career Adviser conversations.</div>
    </div>
    <div class="legal-point">
        <i class="fas fa-trash-can"></i>
        <div><strong>You can leave any time</strong>Deleting your account in Settings removes your profile, results and files.</div>
    </div>
@endsection

@section('content')
    <section>
        <h2 id="introduction">Introduction</h2>
        <p>
            CareerPath BN ("we", "our", "us") is a career guidance platform for students of
            Politeknik Brunei, developed in the School of Information and Communication
            Technology. We respect your privacy and are committed to protecting your personal
            data. This policy explains what we collect, why we collect it, who can see it, and
            how you can control it. It applies to everyone who uses the Platform: students,
            lecturers and administrators.
        </p>
        <p>
            Please read it together with our <a href="{{ route('terms') }}">Terms of Service</a>.
        </p>
    </section>

    <section>
        <h2 id="information-we-collect">Information We Collect</h2>
        <p>We only collect information that you give us or that the Platform needs in order to work.</p>

        <h3>Information you give us</h3>
        <div class="legal-grid">
            <div class="legal-item">
                <strong><i class="fas fa-id-card"></i> Account</strong>
                First and last name, email address, password (stored only as a one-way hash) and role
                (student, lecturer or administrator).
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-user-graduate"></i> Student profile</strong>
                Student ID, programme, CGPA, phone number with its country, date of birth,
                nationality and profile picture.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-layer-group"></i> Skills and competencies</strong>
                BIICF competencies with your proficiency levels, and any extra skills you add yourself.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-heart"></i> Interests</strong>
                The interests you pick from our list and any others you add.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-diagram-project"></i> Projects</strong>
                Titles, descriptions, your role, technologies used, dates, achievements and links.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-certificate"></i> Certifications</strong>
                Name, issuing organisation, dates, and any evidence file you upload (kept private).
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-bullseye"></i> Career aspirations</strong>
                Your career goals, vision statement and long-term goals.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-flag-checkered"></i> Milestones</strong>
                Titles, categories, target dates, progress, and any proof file you upload (kept private).
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-comments"></i> Career Adviser</strong>
                The questions you ask the Career Adviser and the answers it gives, saved to your account.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-rectangle-ad"></i> Preferences</strong>
                Your advertising choice, where your plan lets you choose.
            </div>
        </div>

        <h3>Information the Platform creates</h3>
        <div class="legal-grid">
            <div class="legal-item">
                <strong><i class="fas fa-compass"></i> Career recommendations</strong>
                Recommended job roles, match and readiness scores, matched competencies, gaps and
                development plans, with a saved copy of the profile details each result was based on.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-circle-check"></i> Verification</strong>
                Whether your email address is confirmed, and whether a lecturer or administrator has
                checked your Student ID (and who did).
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-people-group"></i> Programme and class</strong>
                The programme and class groups you belong to, and which lecturers are assigned to them.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-crown"></i> Plan and access</strong>
                Your plan (Free or Premium), any trial, grant or sponsorship with its start and end
                dates, and how many times you have used limited features.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-bell"></i> Notifications</strong>
                Messages about your account, progress and Premium, and whether you have read them.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-clock-rotate-left"></i> Activity</strong>
                A log of actions on your account (such as updating your profile), with the date,
                IP address and browser type; your last login and last active time; and which
                sections you have opened, so we can mark what is new.
            </div>
        </div>
    </section>

    <section>
        <h2 id="how-we-use">How We Use Your Information</h2>
        <ul>
            <li>To provide the service: career recommendations, the Career Adviser, the BIICF Explorer, milestones and your profile.</li>
            <li>To calculate your profile completion and career readiness.</li>
            <li>To apply your plan, trial or sponsorship (for example, how many recommendations you can generate).</li>
            <li>To let your lecturers and administrators follow your progress and verify your Student ID.</li>
            <li>To send you notifications and account emails, such as email confirmation and password reset links.</li>
            <li>To keep accounts secure, investigate misuse, and fix problems.</li>
            <li>To show advertisements as described in <a href="#advertising">Advertising</a>.</li>
        </ul>
        <div class="callout callout--gold">
            <i class="fas fa-hand-holding-heart"></i>
            <p>We do <strong>not</strong> sell your personal data, and we do not use it to build advertising profiles.</p>
        </div>
    </section>

    <section>
        <h2 id="ai-processing">AI Processing</h2>
        <p>
            When you generate career recommendations or ask the Career Adviser a question, the
            career-related parts of your profile are sent to an AI model to produce the answer.
            The request goes through <strong>OpenRouter, Inc.</strong>, an AI routing service, to
            <strong>Groq, Inc.</strong>, which runs the model.
        </p>
        <p>What is sent:</p>
        <ul>
            <li>Your programme, CGPA and any academic records.</li>
            <li>Your competencies, skills, interests, projects, certifications and career aspirations.</li>
            <li>For the Career Adviser, your question, your recent messages in the conversation, and your latest recommendations.</li>
        </ul>
        <div class="callout">
            <i class="fas fa-user-shield"></i>
            <p>
                <strong>Never sent:</strong> your name, email address, Student ID, phone number,
                date of birth, nationality, profile picture or any uploaded file.
            </p>
        </div>
        <p>
            These providers process the request under their own terms and privacy policies. If you
            do not want your career details sent to them, do not use the recommendation or Career
            Adviser features; the rest of the Platform still works.
        </p>
    </section>

    <section>
        <h2 id="who-can-see">Who Can See Your Data</h2>
        <div class="legal-grid">
            <div class="legal-item">
                <strong><i class="fas fa-user"></i> You</strong>
                Everything on your account: profile, recommendations, Career Adviser conversation and files.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-chalkboard-user"></i> Your lecturers</strong>
                Only students in the classes they are assigned to. They can see your profile,
                academic details, competencies, interests, projects, certifications, milestones and
                recommendations, and can verify your Student ID.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-user-gear"></i> Administrators</strong>
                All accounts, to manage users, classes, plans, sponsorships and the BIICF data.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-users-slash"></i> Other students</strong>
                Cannot see your data at all.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-handshake"></i> Sponsors</strong>
                Fund Premium for a group of students. They receive no individual student data,
                results or conversations.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-rectangle-ad"></i> Advertisers</strong>
                Receive no data about you. We do not record which advertisements you see or click.
            </div>
        </div>
        <div class="callout callout--gold">
            <i class="fas fa-lock"></i>
            <p>
                Lecturers and administrators can see <strong>that</strong> you have used the Career
                Adviser and how many messages you have sent, but they <strong>cannot read your
                conversation</strong>.
            </p>
        </div>
    </section>

    <section>
        <h2 id="service-providers">Service Providers</h2>
        <p>We rely on a small number of outside services to run the Platform:</p>
        <ul>
            <li><strong>OpenRouter and Groq</strong> process AI requests, as described in <a href="#ai-processing">AI Processing</a>.</li>
            <li><strong>Brevo</strong> delivers our account emails (email confirmation and password reset) from <strong>noreply@careerpathbn.tech</strong>. It receives your email address and the message.</li>
            <li><strong>Our hosting provider</strong> stores the Platform's database and files.</li>
            <li><strong>Google Fonts and Font Awesome</strong> supply the site's fonts and icons from public networks. Like any website, these requests share your IP address with those services, but they do not set cookies.</li>
        </ul>
    </section>

    <section>
        <h2 id="advertising">Advertising</h2>
        <p>
            Advertisements from partner organisations may appear on student pages. They are chosen
            by your programme or class group, <strong>never by your personal profile</strong>.
        </p>
        <ul>
            <li><strong>Free plan:</strong> advertising helps keep free access free, so it is shown and cannot be switched off.</li>
            <li><strong>Premium:</strong> advertising is off by default. You can turn it on or off at any time in Settings.</li>
            <li><strong>Lecturers and administrators</strong> never see advertisements.</li>
        </ul>
        <p>
            Clicking an advertisement takes you to the advertiser's own website, which has its own
            privacy policy.
        </p>
    </section>

    <section>
        <h2 id="storage-security">Data Storage and Security</h2>
        <ul>
            <li>Passwords are stored only as a one-way hash (bcrypt); nobody, including staff, can see yours.</li>
            <li>New accounts must confirm their email address before using the Platform.</li>
            <li>Certification evidence and milestone proof files are kept on private storage and only shown to you and authorised staff after signing in.</li>
            <li>Profile pictures are stored publicly so they can be shown next to your name.</li>
            <li>Signed-in sessions end after a period of inactivity, and deleting your account asks for your password again.</li>
        </ul>
        <p>
            We take reasonable technical and organisational measures to protect your data, but no
            system is completely secure. Please use a strong password that you do not use anywhere else.
        </p>
    </section>

    <section>
        <h2 id="retention">Data Retention</h2>
        <p>
            We keep your data for as long as your account exists. When you delete your account
            from Settings, your profile, recommendations, Career Adviser conversations, milestones,
            certifications and uploaded files are permanently removed. Anonymised totals (for
            example, how many recommendations were generated) may be kept for reporting.
        </p>
        <p>
            Administrators may also remove accounts, for example when a student leaves Politeknik
            Brunei or an account breaks our <a href="{{ route('terms') }}">Terms of Service</a>.
        </p>
    </section>

    <section>
        <h2 id="cookies">Cookies and Browser Storage</h2>
        <p>We only use cookies that the site needs in order to work:</p>
        <ul>
            <li><strong>Session cookie</strong>: keeps you signed in during your visit and expires when you are inactive or sign out.</li>
            <li><strong>Security (CSRF) cookie</strong>: protects your forms from being submitted by other websites.</li>
            <li><strong>"Remember me" cookie</strong>: only set if you tick "Remember me" when logging in, so you stay signed in on that device.</li>
            <li><strong>Cookie notice</strong>: remembers that you have dismissed the cookie banner.</li>
        </ul>
        <p>
            The site also keeps a few small notes in your browser's session storage, such as which
            tips you have already seen, so they are not repeated. These are deleted when you close
            the tab.
        </p>
        <p>
            We do not use analytics, advertising or third-party tracking cookies. Clearing your
            cookies signs you out, and the cookie notice will appear again.
        </p>
    </section>

    <section>
        <h2 id="your-rights">Your Rights and Choices</h2>
        <ul>
            <li><strong>See</strong> your data at any time on your profile page.</li>
            <li><strong>Correct</strong> it by editing your profile. If your Student ID has been verified, ask your lecturer or an administrator to remove the verification first.</li>
            <li><strong>Download</strong> your profile as a PDF report.</li>
            <li><strong>Delete</strong> your account and its data from Settings.</li>
            <li><strong>Choose</strong> whether to see advertisements, if you are on Premium.</li>
            <li><strong>Ask</strong> us how your data is used, using the contact details below.</li>
        </ul>
    </section>

    <section>
        <h2 id="changes">Changes to This Policy</h2>
        <p>
            We may update this policy as the Platform changes. The date at the top of the page shows
            the latest version, and important changes will be announced on the Platform. If you keep
            using CareerPath BN after an update, the updated policy applies.
        </p>
    </section>

    <section>
        <h2 id="contact">Contact</h2>
        <div class="legal-contact">
            For questions about this Privacy Policy or your data, contact:
            <div class="legal-contact-rows">
                <div class="legal-contact-row">
                    <i class="fas fa-building-columns"></i>
                    <span><strong>School of Information and Communication Technology</strong><br>Politeknik Brunei, Block 2E, Ong Sum Ping Condominium, BA1311</span>
                </div>
                <div class="legal-contact-row">
                    <i class="fas fa-envelope"></i>
                    <span><strong>contact@pb.edu.bn</strong> (Politeknik Brunei, for study and school matters)</span>
                </div>
                <div class="legal-contact-row">
                    <i class="fas fa-envelope-open-text"></i>
                    <span><strong>careerpathbn@gmail.com</strong> (CareerPath BN business email, for help with this website)</span>
                </div>
                <div class="legal-contact-row">
                    <i class="fas fa-phone"></i>
                    <span><strong>+673 2234630</strong></span>
                </div>
            </div>
        </div>
    </section>
@endsection
