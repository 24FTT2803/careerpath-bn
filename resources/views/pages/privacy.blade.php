@extends('layouts.guest')

@section('title', 'Privacy Policy')

@section('content')
    <h1>Privacy Policy</h1>
    <span class="date">Last updated: {{ date('F d, Y') }}</span>

    <h2>1. Introduction</h2>
    <p>CareerPath BN ("we", "our", "us") respects your privacy and is committed to protecting your personal data. This privacy policy explains how we collect, use, and safeguard your information when you use our platform.</p>

    <h2>2. Information We Collect</h2>
    <ul>
        <li><strong>Personal Information:</strong> Name, email address, student ID, programme, and academic information.</li>
        <li><strong>Skills & Competencies:</strong> Technical skills, interests, certifications, and project experience.</li>
        <li><strong>Usage Data:</strong> Information about how you interact with our platform.</li>
    </ul>

    <h2>3. How We Use Your Information</h2>
    <ul>
        <li>To provide career recommendations and guidance.</li>
        <li>To improve our platform and services.</li>
        <li>To communicate with you about your career progress.</li>
        <li>To generate analytics and reports (anonymized).</li>
    </ul>

    <h2>4. Data Storage & Security</h2>
    <p>Your data is stored securely in our database hosted on cloud infrastructure. We implement appropriate technical and organizational measures to protect your personal data.</p>

    <h2>5. Your Rights</h2>
    <p>You have the right to:</p>
    <ul>
        <li>Access your personal data</li>
        <li>Request correction of your personal data</li>
        <li>Request deletion of your personal data</li>
        <li>Withdraw consent at any time</li>
    </ul>

    <h2>6. Contact Us</h2>
    <p>Email: <strong>sict@pb.edu.bn</strong></p>
    <p>Address: <strong>Politeknik Brunei, Jalan Ong Sum Ping, Bandar Seri Begawan</strong></p>

    <a href="{{ url('/register') }}" class="back-link">← Back to Registration</a>
@endsection