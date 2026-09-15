@extends('layouts.guest')

@section('title', 'Terms of Service')

@section('content')
    <h1>Terms of Service</h1>
    <span class="date">Last updated: {{ date('F d, Y') }}</span>

    <h2>1. Acceptance of Terms</h2>
    <p>
        By creating an account on CareerPath BN ("the Platform"), you agree to be bound by
        these Terms of Service. If you do not agree to these terms, please do not use the
        Platform. The Platform is operated by Politeknik Brunei in collaboration with the
        Authority for Info-communications Technology Industry of Brunei Darussalam (AITI).
    </p>

    <h2>2. Description of the Service</h2>
    <p>
        CareerPath BN is an AI-assisted career guidance platform for students of Politeknik
        Brunei. It helps you identify suitable ICT career pathways based on your academic
        background, competencies, interests, projects and career aspirations, aligned with
        the Brunei ICT Industry Competency Framework (BIICF).
    </p>
    <p>The Platform provides:</p>
    <ul>
        <li>Personal career recommendations generated using an AI model, with your match score, matched skills, competency gaps and a suggested development plan.</li>
        <li>A Career Adviser chatbot that answers questions about your recommendations, competency gaps and BIICF job roles.</li>
        <li>A BIICF Explorer for browsing sub-sectors, job roles, competencies, proficiency levels and training.</li>
        <li>Tools for tracking personal milestones, projects and certifications.</li>
        <li>Exportable PDF reports of your career profile and recommendations.</li>
    </ul>
    <p>
        Recommendation scores and AI responses are advisory only. They do not guarantee
        employment, admission, certification or any particular career outcome.
    </p>

    <h2>3. Eligibility and User Accounts</h2>
    <ul>
        <li>You must be a current student or a member of staff of Politeknik Brunei to register.</li>
        <li>Registration is limited to email addresses from <strong>gmail.com</strong>, <strong>pb.edu.bn</strong> or <strong>student.pb.edu.bn</strong>. Lecturer and administrator accounts are limited to <strong>gmail.com</strong> and <strong>pb.edu.bn</strong>.</li>
        <li>You are responsible for maintaining the confidentiality of your login credentials.</li>
        <li>You are responsible for all activity that occurs under your account.</li>
        <li>You must provide accurate and complete information in your profile. Providing false academic records, certifications or qualifications is a violation of these terms.</li>
        <li>You may delete your account at any time from the Settings page. Deletion is permanent and removes your profile, recommendations, conversations, milestones, certifications and uploaded files.</li>
    </ul>

    <h2>4. User Conduct</h2>
    <p>You agree to use the Platform only for lawful purposes and in a way that does not infringe the rights of others. In particular, you agree not to:</p>
    <ul>
        <li>Provide false, misleading or fabricated information about your academic or professional record.</li>
        <li>Upload files that contain malware, are unlawful, or infringe on the intellectual property of others.</li>
        <li>Attempt to gain unauthorised access to any part of the Platform or another user's account.</li>
        <li>Use the Career Adviser or any AI feature to harass, abuse, defame or harm others, or to generate content that violates Brunei law.</li>
        <li>Attempt to reverse-engineer, scrape or overload the Platform or the AI services it uses.</li>
    </ul>

    <h2>5. Uploaded Content</h2>
    <p>
        You may upload profile pictures, project evidence, and certification or milestone
        proof files. By uploading, you confirm that you have the right to share that content.
        Uploaded files are stored on Politeknik Brunei's servers. Certification evidence and
        milestone proofs are stored privately and are only accessible to you and, where
        relevant, to authorised staff for review. Profile pictures are stored on a public
        disk and served via a URL.
    </p>
    <p>
        Do not upload documents containing sensitive personal data (such as identity card
        scans) that are not required for the certification or milestone being verified.
    </p>

    <h2>6. AI Features and Third-Party Services</h2>
    <p>
        Career Recommendations and Career Adviser responses are generated using a large
        language model hosted by Groq, Inc., a third-party AI inference provider. When you
        trigger a recommendation or ask the Career Adviser a question, relevant parts of
        your profile (programme, CGPA, competencies, interests, projects, certifications,
        aspirations, and — for the Adviser — your recent message history) are sent to that
        provider. Your name, email address, student ID and other direct identifiers are not
        sent.
    </p>
    <p>
        AI-generated output may be incomplete, inaccurate or out of date. You are
        responsible for verifying any career information before relying on it.
    </p>

    <h2>7. Advertising</h2>
    <p>
        The Platform may display advertisements from partner organisations. Advertising is
        <strong>off by default</strong> and only appears if you choose to enable it in your
        Settings. You can withdraw this consent at any time. Advertisements are never shown
        to staff accounts.
    </p>

    <h2>8. Sponsorship and Plans</h2>
    <p>
        Access to certain features (such as unlimited recommendation generation, download of
        PDF reports, or Career Adviser history) may depend on the plan assigned to your
        account. Plans are granted by an administrator, or funded by a sponsoring
        organisation for a group of students. Sponsors do not receive access to your
        individual profile data.
    </p>

    <h2>9. Availability</h2>
    <p>
        The Platform is provided on an "as is" and "as available" basis. Features may be
        temporarily disabled for maintenance. AI features depend on third-party services
        and may be intermittently unavailable. We make no guarantee of continuous
        availability.
    </p>

    <h2>10. Changes to These Terms</h2>
    <p>
        We may update these Terms from time to time. Material changes will be communicated
        through the Platform. Continued use after an update constitutes acceptance of the
        revised terms.
    </p>

    <h2>11. Contact</h2>
    <p>
        For questions about these Terms, contact:<br>
        <strong>School of Information and Communication Technology</strong><br>
        Politeknik Brunei, Block 2E, Ong Sum Ping Condominium, BA1311<br>
        Email: <strong>contact@pb.edu.bn</strong><br>
        Phone: <strong>+673 2234630</strong>
    </p>

    <a href="{{ url('/register') }}" class="back-link">← Back to Registration</a>
@endsection