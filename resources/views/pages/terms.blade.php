@extends('layouts.guest')

@section('title', 'Terms of Service')

@section('content')
    <h1>Terms of Service</h1>
    <span class="date">Last updated: {{ date('F d, Y') }}</span>

    <h2>1. Acceptance of Terms</h2>
    <p>By using CareerPath BN ("the Platform"), you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use the Platform.</p>

    <h2>2. Description of Service</h2>
    <p>CareerPath BN is an AI-powered career guidance platform designed to help students identify suitable career pathways based on their interests, academic performance, competencies, and project experience. The Platform is aligned with the Brunei ICT Industry Competency Framework (BIICF).</p>

    <h2>3. User Accounts</h2>
    <ul>
        <li>You must be a student or staff member of Politeknik Brunei to create an account.</li>
        <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
        <li>You are responsible for all activities that occur under your account.</li>
        <li>You must provide accurate and complete information when creating your profile.</li>
    </ul>

    <h2>4. User Conduct</h2>
    <p>You agree to use the Platform only for lawful purposes and in a way that does not infringe the rights of others.</p>
    <ul>
        <li>Do not provide false or misleading information.</li>
        <li>Do not attempt to gain unauthorized access to the Platform.</li>
        <li>Do not use the Platform to harass, abuse, or harm others.</li>
        <li>Do not upload or share malicious content.</li>
    </ul>

    <h2>5. Disclaimer of Warranties</h2>
    <p>The Platform is provided on an "as is" and "as available" basis. We make no warranties, expressed or implied, regarding the accuracy, reliability, or availability of the Platform.</p>

    <h2>6. Contact Us</h2>
    <p>Email: <strong>sict@pb.edu.bn</strong></p>
    <p>Address: <strong>Politeknik Brunei, Jalan Ong Sum Ping, Bandar Seri Begawan</strong></p>

    <a href="{{ url('/register') }}" class="back-link">← Back to Registration</a>
@endsection