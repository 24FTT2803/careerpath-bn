<?php

namespace Database\Seeders;

use App\Models\BiicfCompetency;
use App\Models\BiicfEntryRequirement;
use App\Models\BiicfJobRole;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\BiicfTraining;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BiicfExplorerSeeder extends Seeder
{
    public function run(): void
    {
        // --- Proficiency Levels (BIICF's common job-level scale) ---
        $levels = [
            ['level_number' => 1, 'name' => 'Entrant', 'description' => 'New to the role; works under supervision applying foundational skills.'],
            ['level_number' => 2, 'name' => 'Specialist', 'description' => 'Performs the role independently with solid working knowledge.'],
            ['level_number' => 3, 'name' => 'Senior', 'description' => 'Handles complex tasks and may guide junior staff.'],
            ['level_number' => 4, 'name' => 'Expert / Management', 'description' => 'Deep expertise; sets direction and manages teams or programmes.'],
        ];
        foreach ($levels as $level) {
            BiicfProficiencyLevel::updateOrCreate(['level_number' => $level['level_number']], $level);
        }
        $entrant = BiicfProficiencyLevel::where('level_number', 1)->first();
        $specialist = BiicfProficiencyLevel::where('level_number', 2)->first();
        $senior = BiicfProficiencyLevel::where('level_number', 3)->first();
        $expert = BiicfProficiencyLevel::where('level_number', 4)->first();

        // --- Sub-sectors (all 6 official BIICF sub-sectors, 2 fully populated) ---
        $subSectorNames = [
            'IT Services',
            'Telecommunications and Network',
            'Applications and Solutions Development',
            'ICT Security',
            'Digital Media',
            'Data and Artificial Intelligence',
        ];
        $subSectors = [];
        foreach ($subSectorNames as $i => $name) {
            $subSectors[$name] = BiicfSubSector::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $i]
            );
        }

        // --- Competencies (small trimmed set, technical + soft skill) ---
        $competencyDefs = [
            ['name' => 'Infrastructure Management', 'type' => 'technical', 'description' => 'Support enterprise computing infrastructure: servers, storage, hardware and software.'],
            ['name' => 'Network Administration and Maintenance', 'type' => 'technical', 'description' => 'Configure, monitor and maintain organisational network systems.'],
            ['name' => 'Security Administration', 'type' => 'technical', 'description' => 'Apply and maintain security controls across IT systems.'],
            ['name' => 'Systems Analysis', 'type' => 'technical', 'description' => 'Analyse business needs and translate them into system requirements.'],
            ['name' => 'Project Management', 'type' => 'technical', 'description' => 'Plan, execute and close IT projects within scope, time and budget.'],
            ['name' => 'Problem Management', 'type' => 'technical', 'description' => 'Identify root causes of incidents and implement lasting fixes.'],
            ['name' => 'Communication', 'type' => 'soft_skill', 'description' => 'Convey information clearly to technical and non-technical stakeholders.'],
            ['name' => 'Problem Solving', 'type' => 'soft_skill', 'description' => 'Apply structured thinking to resolve issues effectively.'],
            ['name' => 'Stakeholder Management', 'type' => 'soft_skill', 'description' => 'Build and manage relationships with internal and external stakeholders.'],
            ['name' => 'Adaptability', 'type' => 'soft_skill', 'description' => 'Adjust effectively to changing priorities and technologies.'],
        ];
        $competencies = [];
        foreach ($competencyDefs as $c) {
            $competencies[$c['name']] = BiicfCompetency::updateOrCreate(
                ['slug' => Str::slug($c['name'])],
                $c
            );
        }

        // --- Trainings ---
        $trainingDefs = [
            ['name' => 'CompTIA A+', 'provider' => 'CompTIA', 'certification_body' => 'CompTIA', 'description' => 'Foundational IT support and troubleshooting certification.'],
            ['name' => 'Diploma in Information Technology', 'provider' => 'Institute of Brunei Technical Education (IBTE)', 'certification_body' => 'IBTE', 'description' => 'BDQF-aligned diploma covering core IT skills.'],
            ['name' => 'Certified Information Systems Security Professional (CISSP)', 'provider' => '(ISC)²', 'certification_body' => '(ISC)²', 'description' => 'Advanced certification for security leadership roles.'],
            ['name' => 'Project Management Professional (PMP)', 'provider' => 'PMI', 'certification_body' => 'PMI', 'description' => 'Globally recognised project management certification.'],
        ];
        $trainings = [];
        foreach ($trainingDefs as $t) {
            $trainings[$t['name']] = BiicfTraining::updateOrCreate(['name' => $t['name']], $t);
        }

        // --- Job Roles: IT Services career path (trimmed but faithful to BIICF's chain) ---
        $itTechnician = BiicfJobRole::updateOrCreate(['slug' => 'it-technician'], [
            'sub_sector_id' => $subSectors['IT Services']->id,
            'title' => 'IT Technician',
            'functional_group' => 'Infrastructure & Support',
            'job_description' => 'Maintains functioning IT equipment and networks, provides support to technology users, and upholds policies around use, security and confidentiality of data.',
            'critical_work_function' => "Install and test new ICT equipment and peripherals\nPerform basic PC hardware repairs and upgrades\nIdentify and install essential software patches\nInstall and maintain standard network cabling\nRecord keeping of incidents and service requests",
            'alternative_titles' => ['Technology Associate', 'IT Generalist', 'Help Desk Specialist', 'IT Support Engineer'],
            'career_path_level' => 0,
            'box_colour' => 'primary',
        ]);

        $itAdmin = BiicfJobRole::updateOrCreate(['slug' => 'it-administrator-manager'], [
            'sub_sector_id' => $subSectors['IT Services']->id,
            'title' => 'IT Administrator / Manager',
            'functional_group' => 'Infrastructure & Support',
            'job_description' => 'Oversees day-to-day IT operations, manages a team of technicians, and ensures IT systems align with business needs.',
            'critical_work_function' => "Supervise IT support staff and escalations\nManage vendor and licensing relationships\nMaintain IT asset inventory and lifecycle\nEnforce IT policies and security standards",
            'alternative_titles' => ['IT Coordinator', 'Network Administrator'],
            'career_path_level' => 1,
            'box_colour' => 'primary',
        ]);

        $systemsEngineer = BiicfJobRole::updateOrCreate(['slug' => 'systems-engineer'], [
            'sub_sector_id' => $subSectors['IT Services']->id,
            'title' => 'Systems Engineer',
            'functional_group' => 'Infrastructure & Support',
            'job_description' => 'Evaluates existing systems, provides technical direction, and plans systems automation for better efficiency.',
            'critical_work_function' => "Evaluate existing systems and provide technical direction\nOversee development of customised software/hardware requirements\nPlan and implement systems automation\nFormulate and design security systems to maintain data safety",
            'alternative_titles' => ['Infrastructure Engineer'],
            'career_path_level' => 2,
            'box_colour' => 'primary',
        ]);

        $projectManager = BiicfJobRole::updateOrCreate(['slug' => 'project-manager'], [
            'sub_sector_id' => $subSectors['IT Services']->id,
            'title' => 'Project Manager',
            'functional_group' => 'Delivery & Operations',
            'job_description' => 'Plans, executes and closes IT projects, managing scope, budget, timeline and stakeholders.',
            'critical_work_function' => "Define project scope, goals and deliverables\nManage project budget and resourcing\nCoordinate cross-functional teams\nReport progress to stakeholders",
            'alternative_titles' => ['IT Project Lead'],
            'career_path_level' => 3,
            'box_colour' => 'primary',
        ]);

        $itOpsManager = BiicfJobRole::updateOrCreate(['slug' => 'it-operations-manager'], [
            'sub_sector_id' => $subSectors['IT Services']->id,
            'title' => 'IT Operations Manager',
            'functional_group' => 'Delivery & Operations',
            'job_description' => 'Oversees IT operations across teams, ensuring service levels, budgets and strategic alignment with the business.',
            'critical_work_function' => "Set and monitor service-level objectives\nManage IT operations budget\nAlign IT operations with business strategy",
            'alternative_titles' => [],
            'career_path_level' => 4,
            'box_colour' => 'light-blue',
        ]);

        $cio = BiicfJobRole::updateOrCreate(['slug' => 'chief-information-officer'], [
            'sub_sector_id' => $subSectors['IT Services']->id,
            'title' => 'Chief Information Officer',
            'functional_group' => 'Leadership',
            'job_description' => 'Sets the organisation-wide technology strategy and leads digital transformation initiatives.',
            'critical_work_function' => "Define enterprise IT and digital strategy\nManage IT governance and risk\nRepresent technology function at executive/board level",
            'alternative_titles' => ['CIO', 'Head of Technology'],
            'career_path_level' => 5,
            'box_colour' => 'light-blue',
        ]);

        // Career path edges (progression chain, mirrors BIICF's diagram)
        $edges = [
            [$itTechnician, $itAdmin],
            [$itAdmin, $systemsEngineer],
            [$systemsEngineer, $projectManager],
            [$projectManager, $itOpsManager],
            [$itOpsManager, $cio],
        ];
        foreach ($edges as [$from, $to]) {
            $from->progressesTo()->syncWithoutDetaching([$to->id]);
        }

        // --- Job Roles: ICT Security (trimmed) ---
        $associateSecurityAnalyst = BiicfJobRole::updateOrCreate(['slug' => 'associate-security-analyst'], [
            'sub_sector_id' => $subSectors['ICT Security']->id,
            'title' => 'Associate Security Analyst',
            'functional_group' => 'Security Operations',
            'job_description' => 'Monitors security systems and assists in identifying and responding to security incidents.',
            'critical_work_function' => "Monitor security alerts and logs\nAssist in incident triage and response\nSupport vulnerability scanning activities",
            'alternative_titles' => ['Junior Security Analyst', 'SOC Analyst'],
            'career_path_level' => 0,
            'box_colour' => 'primary',
        ]);

        $securityEngineer = BiicfJobRole::updateOrCreate(['slug' => 'security-engineer'], [
            'sub_sector_id' => $subSectors['ICT Security']->id,
            'title' => 'Security Engineer',
            'functional_group' => 'Security Operations',
            'job_description' => 'Designs, implements and maintains security controls to protect information and systems.',
            'critical_work_function' => "Implement and maintain security infrastructure\nConduct security assessments and audits\nRespond to and remediate security incidents",
            'alternative_titles' => ['Cyber Security Engineer'],
            'career_path_level' => 1,
            'box_colour' => 'primary',
        ]);

        $cyberRiskAnalyst = BiicfJobRole::updateOrCreate(['slug' => 'cyber-risk-analyst'], [
            'sub_sector_id' => $subSectors['ICT Security']->id,
            'title' => 'Cyber Risk Analyst',
            'functional_group' => 'Governance, Risk & Compliance',
            'job_description' => 'Assesses cyber risk exposure and advises the organisation on risk mitigation strategies.',
            'critical_work_function' => "Conduct risk assessments across systems and processes\nMaintain risk register and treatment plans\nReport risk posture to management",
            'alternative_titles' => [],
            'career_path_level' => 2,
            'box_colour' => 'primary',
        ]);

        $associateSecurityAnalyst->progressesTo()->syncWithoutDetaching([$securityEngineer->id]);
        $securityEngineer->progressesTo()->syncWithoutDetaching([$cyberRiskAnalyst->id]);

        // --- Competency mappings (job role -> competency -> proficiency level) ---
        $mappings = [
            [$itTechnician, 'Infrastructure Management', $entrant, true],
            [$itTechnician, 'Communication', $entrant, false],
            [$itTechnician, 'Problem Solving', $entrant, true],

            [$itAdmin, 'Network Administration and Maintenance', $specialist, true],
            [$itAdmin, 'Stakeholder Management', $specialist, false],

            [$systemsEngineer, 'Systems Analysis', $specialist, true],
            [$systemsEngineer, 'Security Administration', $specialist, false],

            [$projectManager, 'Project Management', $senior, true],
            [$projectManager, 'Stakeholder Management', $senior, true],

            [$itOpsManager, 'Project Management', $expert, true],
            [$itOpsManager, 'Adaptability', $expert, false],

            [$cio, 'Stakeholder Management', $expert, true],

            [$associateSecurityAnalyst, 'Security Administration', $entrant, true],
            [$associateSecurityAnalyst, 'Problem Solving', $entrant, false],

            [$securityEngineer, 'Security Administration', $specialist, true],
            [$securityEngineer, 'Problem Management', $specialist, true],

            [$cyberRiskAnalyst, 'Communication', $senior, true],
            [$cyberRiskAnalyst, 'Problem Management', $senior, true],
        ];
        foreach ($mappings as [$role, $compName, $level, $isCore]) {
            $role->competencies()->syncWithoutDetaching([
                $competencies[$compName]->id => [
                    'proficiency_level_id' => $level->id,
                    'is_core' => $isCore,
                ],
            ]);
        }

        // --- Entry requirements ---
        $entryReqs = [
            [$itTechnician, 'BDQF Level 3', 'IT or related field', 'Relevant vocational/apprenticeship route accepted with demonstrated hands-on skills.', null],
            [$itAdmin, 'BDQF Level 4', 'IT or related field', null, '2+ years as IT Technician or equivalent'],
            [$systemsEngineer, 'BDQF Level 6', 'IT, Computer Science, Software Engineering or related field', null, '3+ years in a technical infrastructure role'],
            [$projectManager, 'BDQF Level 6', 'IT, Business or related field', 'PMP or equivalent certification may substitute for part of the degree requirement.', '3+ years coordinating IT projects'],
            [$itOpsManager, 'BDQF Level 6', 'IT or Business Management', null, '5+ years in IT operations, including supervisory experience'],
            [$cio, 'BDQF Level 7', 'IT, Business Administration or related field', null, '10+ years in progressively senior technology leadership roles'],
            [$associateSecurityAnalyst, 'BDQF Level 4', 'IT, Cyber Security or related field', null, null],
            [$securityEngineer, 'BDQF Level 6', 'IT, Cyber Security or related field', null, '2+ years in a security operations role'],
            [$cyberRiskAnalyst, 'BDQF Level 6', 'IT, Risk Management or related field', null, '3+ years in security or risk-related roles'],
        ];
        foreach ($entryReqs as [$role, $bdqf, $field, $altPath, $years]) {
            BiicfEntryRequirement::updateOrCreate(['job_role_id' => $role->id], [
                'bdqf_level' => $bdqf,
                'field_of_study' => $field,
                'alternative_pathway' => $altPath,
                'years_experience' => $years,
            ]);
        }

        // --- Recommended trainings per role ---
        $itTechnician->trainings()->syncWithoutDetaching([$trainings['CompTIA A+']->id, $trainings['Diploma in Information Technology']->id]);
        $projectManager->trainings()->syncWithoutDetaching([$trainings['Project Management Professional (PMP)']->id]);
        $itOpsManager->trainings()->syncWithoutDetaching([$trainings['Project Management Professional (PMP)']->id]);
        $securityEngineer->trainings()->syncWithoutDetaching([$trainings['Certified Information Systems Security Professional (CISSP)']->id]);
        $cyberRiskAnalyst->trainings()->syncWithoutDetaching([$trainings['Certified Information Systems Security Professional (CISSP)']->id]);
    }
}