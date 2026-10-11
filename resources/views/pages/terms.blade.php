@extends('layouts.guest')

@section('title', 'Terms of Service')
@section('eyebrow', 'The rules')
@section('icon', 'fa-file-contract')
@section('updated', '10 October 2026')
@section('lead', 'The agreement between you and CareerPath BN: what the Platform offers, what we ask of you, and what to expect from us.')

@section('summary')
    <div class="legal-point">
        <i class="fas fa-graduation-cap"></i>
        <div><strong>For Politeknik Brunei</strong>Open to current students and staff with an approved email address.</div>
    </div>
    <div class="legal-point">
        <i class="fas fa-lightbulb"></i>
        <div><strong>Guidance, not a guarantee</strong>Recommendations and AI answers are advice to help you decide, not promises.</div>
    </div>
    <div class="legal-point">
        <i class="fas fa-scale-balanced"></i>
        <div><strong>Be honest and respectful</strong>Enter true information, upload only what you may share, and do not misuse the AI.</div>
    </div>
    <div class="legal-point">
        <i class="fas fa-crown"></i>
        <div><strong>No payments are taken</strong>Premium comes from a sponsor, an administrator, a trial, or the demonstration upgrade.</div>
    </div>
@endsection

@section('content')
    <section>
        <h2 id="acceptance">Acceptance of Terms</h2>
        <p>
            By creating an account on or using CareerPath BN ("the Platform"), you agree to these
            Terms of Service and to our <a href="{{ route('privacy') }}">Privacy Policy</a>. If you
            do not agree, please do not use the Platform.
        </p>
        <p>
            The Platform is operated by Politeknik Brunei in collaboration with the Authority for
            Info-communications Technology Industry of Brunei Darussalam (AITI).
        </p>
    </section>

    <section>
        <h2 id="the-service">The Service</h2>
        <p>
            CareerPath BN is an AI-assisted career guidance platform for students of Politeknik
            Brunei. It helps you find ICT career pathways that suit your academic background,
            competencies, interests, projects and goals, aligned with the Brunei ICT Industry
            Competency Framework (BIICF). It includes:
        </p>
        <div class="legal-grid">
            <div class="legal-item">
                <strong><i class="fas fa-compass"></i> Career recommendations</strong>
                Matched BIICF job roles with match and readiness scores, matched competencies, gaps and a development plan.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-comments"></i> Career Adviser</strong>
                A chat assistant for questions about your results, competency gaps and BIICF job roles.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-sitemap"></i> BIICF Explorer</strong>
                Browse sub-sectors, job roles, competencies, proficiency levels and training, and compare roles.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-flag-checkered"></i> Planning tools</strong>
                Track milestones, projects and certifications, and see your profile completion.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-lightbulb"></i> Innovation Lab</strong>
                News and opportunities shared by our partners and the CareerPath BN team.
            </div>
            <div class="legal-item">
                <strong><i class="fas fa-file-pdf"></i> PDF reports</strong>
                Download your career profile and, on Premium, your recommendation reports.
            </div>
        </div>
        <div class="callout callout--gold">
            <i class="fas fa-circle-info"></i>
            <p>
                Recommendations, scores and Career Adviser answers are <strong>advisory only</strong>.
                They do not guarantee employment, admission, certification or any particular career
                outcome. Please talk to your lecturers before making important decisions.
            </p>
        </div>
    </section>

    <section>
        <h2 id="accounts">Eligibility and Accounts</h2>
        <ul>
            <li>You must be a current student or member of staff of Politeknik Brunei.</li>
            <li>Students sign up themselves with an email address from <strong>gmail.com</strong>, <strong>student.pb.edu.bn</strong> or <strong>pb.edu.bn</strong>, and must confirm it before using the Platform.</li>
            <li>Lecturer and administrator accounts are created by an administrator, using a <strong>gmail.com</strong> or <strong>pb.edu.bn</strong> address.</li>
            <li>Keep your password private. You are responsible for everything done through your account, so sign out on shared computers.</li>
            <li>Your profile must be accurate and your own. Lecturers or administrators may check your Student ID; once it is verified it is locked, and staff can unlock it if it needs correcting.</li>
            <li>You may delete your account at any time from Settings. Deletion is permanent and removes your profile, results, conversations, milestones, certifications and uploaded files.</li>
        </ul>
    </section>

    <section>
        <h2 id="conduct">Acceptable Use</h2>
        <p>Use the Platform lawfully and respectfully. In particular, you must not:</p>
        <ul>
            <li>Enter false, misleading or someone else's academic, professional or personal details.</li>
            <li>Upload files that contain malware, are unlawful, or infringe anyone else's rights.</li>
            <li>Try to access another user's account or any part of the Platform you are not allowed to.</li>
            <li>Use the Career Adviser or any AI feature to harass, abuse or harm others, or to produce content that breaks the laws of Brunei Darussalam.</li>
            <li>Try to get around plan limits, or scrape, overload, or reverse-engineer the Platform or the AI services it uses.</li>
            <li>Use offensive language in your profile. Names and other details are checked for profanity.</li>
        </ul>
    </section>

    <section>
        <h2 id="uploads">Your Content and Uploads</h2>
        <p>
            You may upload a profile picture and evidence files for your certifications and
            milestones. By uploading, you confirm you have the right to share them. Your content
            stays yours; you allow us to store and display it only to provide the service.
        </p>
        <ul>
            <li>Certification evidence and milestone proof files are private: only you and authorised staff can open them.</li>
            <li>Profile pictures are shown next to your name and can be reached by their web address.</li>
            <li>Do not upload documents with sensitive personal details (such as identity card scans) that are not needed.</li>
        </ul>
    </section>

    <section>
        <h2 id="ai">AI Features</h2>
        <p>
            Career recommendations and Career Adviser answers are produced by a large language model.
            Requests go through OpenRouter, Inc. to Groq, Inc., which runs the model. Only the
            career-related parts of your profile are sent; your name, email address, Student ID,
            phone number and files are not. See our <a href="{{ route('privacy') }}#ai-processing">Privacy Policy</a> for details.
        </p>
        <p>
            AI output can be incomplete, inaccurate or out of date. Check important information,
            such as entry requirements or certifications, with the official BIICF material or your
            lecturers before relying on it.
        </p>
    </section>

    <section>
        <h2 id="plans">Plans, Trials and Sponsorship</h2>
        <p>Every student starts on the <strong>Free</strong> plan. <strong>Premium</strong> adds more, such as extra results per generation, unlimited generations, recommendation history, role comparison and PDF recommendation reports. The current limits are shown on your Settings page.</p>
        <ul>
            <li><strong>Sponsorship:</strong> an organisation may fund Premium for a group of students. Sponsored access is always Premium, and sponsors never receive your individual data.</li>
            <li><strong>Administrator grants and trials:</strong> an administrator may give you Premium or a trial, for a set period or with no end date.</li>
            <li><strong>Demonstration upgrade:</strong> the upgrade button in Settings is a demonstration. <strong>No payment is requested or taken</strong> on CareerPath BN.</li>
            <li><strong>When Premium ends</strong> (it expires, is withdrawn, or a sponsor stops), your account returns to the Free plan and you are notified. Your saved data is kept, but Premium-only features close.</li>
        </ul>
        <p>Plan features and limits may be changed by administrators as the Platform develops.</p>
    </section>

    <section>
        <h2 id="advertising">Advertising</h2>
        <p>
            Partner advertisements may appear on student pages. On the Free plan they are part of
            how free access is supported and cannot be turned off. On Premium they are off by default
            and you can choose in Settings. Staff never see advertisements. We are not responsible
            for the content of advertisers' own websites.
        </p>
    </section>

    <section>
        <h2 id="ip">Intellectual Property</h2>
        <p>
            The CareerPath BN name, logo, design and software belong to the CareerPath BN team at
            Politeknik Brunei. BIICF content is from AITI's ICT Industry Competency Framework, and
            partner logos belong to their owners. You may use the Platform and your own reports for
            your studies and career planning, but you may not copy or republish the Platform itself.
        </p>
    </section>

    <section>
        <h2 id="suspension">Suspension and Removal</h2>
        <p>
            Administrators may suspend or remove an account that breaks these Terms, is used to
            harm others, or no longer belongs to a current student or staff member. Where
            reasonable, we will tell you why.
        </p>
    </section>

    <section>
        <h2 id="availability">Availability and Liability</h2>
        <p>
            The Platform is provided "as is" and "as available". Features may be paused for
            maintenance, and AI features depend on outside services that may sometimes be
            unavailable. As far as the law allows, CareerPath BN and Politeknik Brunei are not
            responsible for decisions you make based on the Platform's guidance, or for losses
            caused by interruptions we could not reasonably prevent.
        </p>
    </section>

    <section>
        <h2 id="changes">Changes to These Terms</h2>
        <p>
            We may update these Terms as the Platform changes. The date at the top shows the latest
            version, and important changes will be announced on the Platform. If you keep using
            CareerPath BN after an update, the updated Terms apply.
        </p>
    </section>

    <section>
        <h2 id="law">Governing Law</h2>
        <p>These Terms are governed by the laws of Brunei Darussalam.</p>
    </section>

    <section>
        <h2 id="contact">Contact</h2>
        <div class="legal-contact">
            For questions about these Terms, contact:
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
