@extends('layouts.guest')

@section('title', 'Privacy Policy')

@section('content')
    <h1>Privacy Policy</h1>
    <span class="date">Last updated: {{ date('F d, Y') }}</span>

    <h2>1. Introduction</h2>
    <p>
        CareerPath BN ("we", "our", "us") respects your privacy and is committed to
        protecting your personal data. This policy explains what we collect, why we
        collect it, who can see it, and how you can control it. It applies to all users
        of the Platform.
    </p>

    <h2>2. Information We Collect</h2>
    <p>We collect the following categories of information:</p>

    <ul>
        <li>
            <strong>Account information:</strong> first name, last name, email address,
            password (stored as a one-way hash), role (student / lecturer / administrator),
            and account timestamps.
        </li>
        <li>
            <strong>Student profile:</strong> student ID, programme, CGPA, phone number
            (stored in international E.164 format), date of birth, nationality, address,
            short biography and profile picture.
        </li>
        <li>
            <strong>Academic records:</strong> institution, programme name, level, dates,
            subjects, grades and achievements.
        </li>
        <li>
            <strong>Competencies and skills:</strong> BIICF competencies with proficiency
            levels, and any additional skills you enter yourself.
        </li>
        <li>
            <strong>Interests:</strong> predefined interests and any additional interests
            you enter.
        </li>
        <li>
            <strong>Projects:</strong> titles, descriptions, roles, technologies used,
            dates, achievements and project URLs.
        </li>
        <li>
            <strong>Certifications:</strong> certification name, issuing organisation,
            issue date, and any evidence file you upload (stored on a private disk).
        </li>
        <li>
            <strong>Aspirations:</strong> career goals, preferred industries, preferred
            work activities, vision, mission and long-term goals.
        </li>
        <li>
            <strong>Milestones:</strong> milestone titles, categories, target dates,
            completion status, and any proof file you upload (stored on a private disk).
        </li>
        <li>
            <strong>Career recommendations:</strong> the AI-generated recommendations,
            match scores, readiness scores, matched skills, competency gaps and
            development plans produced for your account.
        </li>
        <li>
            <strong>Career Adviser conversations:</strong> the questions you ask and the
            answers returned, stored against your account.
        </li>
        <li>
            <strong>Usage and activity:</strong> notifications generated for your account,
            feature usage records (used to enforce plan quotas), and a "last active"
            timestamp (used to display an online indicator on staff dashboards).
        </li>
        <li>
            <strong>Advertising preferences:</strong> whether you have opted in to seeing
            advertisements.
        </li>
    </ul>

    <h2>3. How We Use Your Information</h2>
    <ul>
        <li>To provide the core service: generating career recommendations, running the Career Adviser, and displaying your profile and progress.</li>
        <li>To calculate your profile completion and career readiness scores.</li>
        <li>To enforce plan and sponsorship entitlements (for example, how many recommendations you can generate).</li>
        <li>To allow authorised staff (lecturers and administrators) to review student progress on an aggregate and per-student basis.</li>
        <li>To send you notifications about your account and progress.</li>
        <li>To improve the Platform and diagnose problems.</li>
        <li>To display advertisements, but only if you have opted in.</li>
    </ul>

    <h2>4. AI Processing and Third-Party Sharing</h2>
    <p>
        When you generate career recommendations or ask the Career Adviser a question,
        relevant parts of your profile are sent to <strong>Groq, Inc.</strong>, a
        third-party AI inference provider, to produce the response. This includes your
        programme, CGPA, academic records, competencies, interests, projects,
        certifications, aspirations, and — for the Career Adviser — your recent message
        history within the current conversation.
    </p>
    <p>
        Your <strong>name, email address, student ID, phone number, profile picture and
        uploaded files are never sent to the AI provider.</strong> We send only the
        information the AI needs to give career guidance.
    </p>
    <p>
        Groq processes this data under its own terms of service. If you do not wish your
        profile data to be sent to Groq, you should not use the recommendation or Career
        Adviser features.
    </p>

    <h2>5. Who Can See Your Data</h2>
    <ul>
        <li><strong>You</strong> — full access to your own profile, recommendations, conversation and files.</li>
        <li><strong>Lecturers and administrators</strong> — can view your profile, academic information, competencies, projects, certifications, milestones, career recommendations and generation history. They can also see that you have used the Career Adviser and how many messages you have exchanged, but <strong>they cannot read the contents of your Career Adviser conversation</strong>.</li>
        <li><strong>Other students</strong> — cannot see your data at all.</li>
        <li><strong>Sponsoring organisations</strong> — provide funding for a group of students but do not receive any individual student data, recommendations or conversations.</li>
        <li><strong>Third-party AI provider (Groq)</strong> — receives the anonymised career-relevant subset of your profile described in section 4, only when you use an AI feature.</li>
    </ul>

    <h2>6. Advertising</h2>
    <p>
        Advertising is disabled by default. If you enable it in Settings, the Platform may
        display advertisements from partner organisations. Advertisements are selected
        based on your organisation group (for example, your school or programme), not on
        your personal profile. You can turn advertising off at any time.
    </p>

    <h2>7. Data Storage and Security</h2>
    <p>
        Your data is stored in the Platform's database and on the servers that host it.
        Passwords are hashed using bcrypt. Certification evidence and milestone proof
        files are stored on a private disk and only served to you or to authorised staff
        through authenticated routes. Profile pictures are stored on a public disk so
        that they can be displayed alongside your name.
    </p>
    <p>
        We take reasonable technical and organisational measures to protect your data, but
        no system is completely secure. Please use a strong, unique password.
    </p>

    <h2>8. Data Retention</h2>
    <p>
        Your data is retained for as long as your account exists. If you delete your
        account from the Settings page, your profile, recommendations, conversations,
        milestones, certifications and uploaded files are permanently removed from the
        database. Certain aggregated or anonymised records may be retained for reporting
        purposes.
    </p>

    <h2>9. Your Rights</h2>
    <p>You have the right to:</p>
    <ul>
        <li>Access your personal data at any time through your profile page.</li>
        <li>Correct your personal data by editing your profile.</li>
        <li>Export your data as a PDF report from your profile page.</li>
        <li>Delete your account and all associated data from the Settings page.</li>
        <li>Withdraw consent for advertising at any time from Settings.</li>
        <li>Request information about how your data is processed by contacting us at the address below.</li>
    </ul>

    <h2>10. Changes to This Policy</h2>
    <p>
        We may update this policy from time to time. Material changes will be communicated
        through the Platform. Continued use after an update constitutes acceptance of the
        revised policy.
    </p>

    <h2>11. Contact</h2>
    <p>
        For questions about this Privacy Policy or to exercise your data rights, contact:<br>
        <strong>School of Information and Communication Technology</strong><br>
        Politeknik Brunei, Block 2E, Ong Sum Ping Condominium, BA1311<br>
        Email: <strong>contact@pb.edu.bn</strong><br>
        Phone: <strong>+673 2234630</strong>
    </p>

    <a href="{{ url('/register') }}" class="back-link">← Back to Registration</a>
@endsection