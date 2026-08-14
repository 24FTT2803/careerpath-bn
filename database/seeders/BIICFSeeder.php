<?php

namespace Database\Seeders;

use App\Models\BIICFCareer;
use Illuminate\Database\Seeder;

class BIICFSeeder extends Seeder
{
    public function run()
    {
        $careers = [
            [
                'job_title' => 'Software Developer',
                'subsector' => 'Software Development',
                'technical_skills' => json_encode(['Python', 'JavaScript', 'SQL', 'Git', 'React']),
                'soft_skills' => json_encode(['Problem Solving', 'Communication', 'Teamwork']),
                'entry_requirements' => json_encode(['Diploma in ICT', 'Programming experience']),
                'recommended_training' => json_encode(['Advanced JavaScript', 'Cloud Computing', 'DevOps']),
                'certifications' => json_encode(['AWS Certified Developer', 'Zend PHP Engineer']),
                'job_description' => 'Design, develop, and maintain software applications.',
                'demand_level' => 'Very High',
            ],
            [
                'job_title' => 'Network Engineer',
                'subsector' => 'Network Infrastructure',
                'technical_skills' => json_encode(['Cisco', 'Routing Protocols', 'Firewall', 'TCP/IP']),
                'soft_skills' => json_encode(['Analytical Thinking', 'Troubleshooting', 'Documentation']),
                'entry_requirements' => json_encode(['Diploma in ICT/CNG', 'Network fundamentals']),
                'recommended_training' => json_encode(['CCNA', 'Network Security']),
                'certifications' => json_encode(['CCNA', 'CompTIA Network+']),
                'job_description' => 'Design, implement, and manage network infrastructure.',
                'demand_level' => 'High',
            ],
            [
                'job_title' => 'Data Analyst',
                'subsector' => 'Data Analytics',
                'technical_skills' => json_encode(['SQL', 'Python', 'Excel', 'Power BI']),
                'soft_skills' => json_encode(['Analytical', 'Problem Solving', 'Communication']),
                'entry_requirements' => json_encode(['Diploma in ICT/DAT', 'Statistics knowledge']),
                'recommended_training' => json_encode(['Data Visualization', 'Machine Learning Basics']),
                'certifications' => json_encode(['Google Data Analytics', 'Microsoft Power BI']),
                'job_description' => 'Analyze and interpret complex data for decision making.',
                'demand_level' => 'Very High',
            ],
            [
                'job_title' => 'Cybersecurity Specialist',
                'subsector' => 'Cybersecurity',
                'technical_skills' => json_encode(['Network Security', 'Penetration Testing', 'Firewall', 'Linux']),
                'soft_skills' => json_encode(['Detail Oriented', 'Problem Solving', 'Curiosity']),
                'entry_requirements' => json_encode(['Diploma in ICT/CNG', 'Security fundamentals']),
                'recommended_training' => json_encode(['Ethical Hacking', 'Cryptography']),
                'certifications' => json_encode(['CEH', 'CompTIA Security+']),
                'job_description' => 'Protect organizations from cyber threats.',
                'demand_level' => 'Very High',
            ],
            [
                'job_title' => 'Cloud Engineer',
                'subsector' => 'Cloud Computing',
                'technical_skills' => json_encode(['AWS', 'Azure', 'Docker', 'Linux']),
                'soft_skills' => json_encode(['Adaptability', 'Problem Solving', 'Automation']),
                'entry_requirements' => json_encode(['Diploma in ICT/CNG', 'Cloud fundamentals']),
                'recommended_training' => json_encode(['AWS Solutions Architect', 'Docker']),
                'certifications' => json_encode(['AWS Certified', 'Azure Administrator']),
                'job_description' => 'Design and manage cloud infrastructure.',
                'demand_level' => 'High',
            ],
        ];

        foreach ($careers as $career) {
            BIICFCareer::create($career);
        }
    }
}