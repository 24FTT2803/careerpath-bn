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
    /** @var array<string, BiicfCompetency> */
    private array $competencies = [];

    /** @var array<int, BiicfProficiencyLevel> */
    private array $levels = [];

    public function run(): void
    {
        $this->seedProficiencyLevels();
        $subSectors = $this->seedSubSectors();
        $this->seedCompetencies();

        $this->seedItServices($subSectors['IT Services']);
        $this->seedTelecom($subSectors['Telecommunications and Network']);
        $this->seedAppsDev($subSectors['Applications and Solutions Development']);
        $this->seedIctSecurity($subSectors['ICT Security']);
        $this->seedDigitalMedia($subSectors['Digital Media']);
        $this->seedDataAi($subSectors['Data and Artificial Intelligence']);
    }

    /**
     * Real BIICF technical proficiency scale: Follow / Assist / Apply / Ensure / Strategise (1-5).
     * Soft-skill proficiency (Basic/Intermediate/Advanced/Expert) is mapped onto the same 1-5
     * scale (2/3/4/5) since this schema shares one scale across both competency types.
     */
    private function seedProficiencyLevels(): void
    {
        $defs = [
            ['level_number' => 1, 'name' => 'Follow', 'description' => 'Demonstrates introductory understanding and, with guidance, applies the competency in a few simple situations.'],
            ['level_number' => 2, 'name' => 'Assist', 'description' => 'Demonstrates basic knowledge and, with guidance, applies the competency in common situations with limited difficulty. (Soft skill equivalent: Basic)'],
            ['level_number' => 3, 'name' => 'Apply', 'description' => 'Demonstrates solid knowledge and applies the competency with minimal guidance in typical situations. (Soft skill equivalent: Intermediate)'],
            ['level_number' => 4, 'name' => 'Ensure', 'description' => 'Demonstrates advanced knowledge and applies the competency in new or complex situations, guiding others. (Soft skill equivalent: Advanced)'],
            ['level_number' => 5, 'name' => 'Strategise', 'description' => 'Demonstrates expert knowledge, develops new approaches and is recognised as an expert. (Soft skill equivalent: Expert)'],
        ];
        foreach ($defs as $d) {
            $this->levels[$d['level_number']] = BiicfProficiencyLevel::updateOrCreate(['level_number' => $d['level_number']], $d);
        }
    }

    private function lvl(int $n): BiicfProficiencyLevel
    {
        return $this->levels[max(1, min(5, $n))];
    }

    /** @return array<string, BiicfSubSector> */
    private function seedSubSectors(): array
    {
        $names = [
            'IT Services',
            'Telecommunications and Network',
            'Applications and Solutions Development',
            'ICT Security',
            'Digital Media',
            'Data and Artificial Intelligence',
        ];
        $out = [];
        foreach ($names as $i => $name) {
            $out[$name] = BiicfSubSector::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'sort_order' => $i]);
        }

        return $out;
    }

    private function seedCompetencies(): void
    {
        $technical = [
            'Business Analysis', 'IT Architecture', 'Application Development', 'Application Integration',
            'Application Support and Enhancement', 'Cloud Computing', 'System Integration', 'Infrastructure Management',
            'Infrastructure Support', 'Infrastructure Design', 'IT Project Management', 'Network Security Management',
            'Information Security Management', 'Vendor Management', 'Service Management', 'Service Level Management',
            'Data Analytics', 'Database Management', 'Telecommunications Network Management',
            'Network Administration and Maintenance', 'Network Configuration', 'Test Planning', 'Fault Management',
            'Business Risk Management', 'Contract Management', 'Procurement', 'IT Asset Management', 'Budgeting',
            'Performance Management', 'Problem Management', 'Process Improvement',
            'Cyber and Data Breach Incident Management', 'Cyber Risk Management', 'Emerging Technology Synthesis',
            'Security Architecture', 'Security Administration', 'Security Governance', 'Security Programme Management',
            'Stakeholder Management', 'Security Implementation', 'Security Planning', 'Software Configuration',
            'Software Design', 'Software Testing', 'User Interface Design', 'Quality Standards',
            'Customer Intelligence Analysis', 'Customer Behaviour Analysis', 'Data and Trend Analytics',
            'Market Research', 'Media and Platform Management',
        ];
        $soft = [
            'Analytical Thinking', 'Decision-Making', 'Communication', 'Work Management', 'Teamwork',
            'People Management', 'Creativity and Innovation', 'Results Orientation', 'Service Orientation',
            'Negotiation', 'Resilience',
        ];
        foreach ($technical as $name) {
            $this->competencies[$name] = BiicfCompetency::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'type' => 'technical', 'description' => null]
            );
        }
        foreach ($soft as $name) {
            $this->competencies[$name] = BiicfCompetency::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'type' => 'soft_skill', 'description' => null]
            );
        }
    }

    private function comp(string $name): BiicfCompetency
    {
        return $this->competencies[$name];
    }

    /**
     * Create/update a job role and attach its competency + entry requirement + training data in one call.
     *
     * @param  array<string,int>  $technical  competency name => proficiency level_number (1-5)
     * @param  array<string,int>  $soft  competency name => proficiency level_number (1-5)
     * @param  array<int,string>  $trainings  training names
     */
    private function role(
        BiicfSubSector $subSector,
        string $title,
        string $slug,
        string $functionalGroup,
        string $jobDescription,
        string $criticalWorkFunction,
        array $altTitles,
        int $careerPathLevel,
        array $technical,
        array $soft,
        ?string $bdqfLevel,
        ?string $fieldOfStudy,
        ?string $altPathway,
        ?string $yearsExperience,
        array $trainings,
        string $boxColour = 'primary',
    ): BiicfJobRole {
        $role = BiicfJobRole::updateOrCreate(['slug' => $slug], [
            'sub_sector_id' => $subSector->id,
            'title' => $title,
            'functional_group' => $functionalGroup,
            'job_description' => $jobDescription,
            'critical_work_function' => $criticalWorkFunction,
            'alternative_titles' => $altTitles,
            'career_path_level' => $careerPathLevel,
            'box_colour' => $boxColour,
        ]);

        foreach ($technical as $name => $level) {
            $role->competencies()->syncWithoutDetaching([
                $this->comp($name)->id => ['proficiency_level_id' => $this->lvl($level)->id, 'is_core' => true],
            ]);
        }
        foreach ($soft as $name => $level) {
            $role->competencies()->syncWithoutDetaching([
                $this->comp($name)->id => ['proficiency_level_id' => $this->lvl($level)->id, 'is_core' => false],
            ]);
        }

        if ($bdqfLevel || $fieldOfStudy) {
            BiicfEntryRequirement::updateOrCreate(['job_role_id' => $role->id], [
                'bdqf_level' => $bdqfLevel,
                'field_of_study' => $fieldOfStudy,
                'alternative_pathway' => $altPathway,
                'years_experience' => $yearsExperience,
            ]);
        }

        foreach ($trainings as $t) {
            $training = BiicfTraining::updateOrCreate(['name' => $t], ['name' => $t]);
            $role->trainings()->syncWithoutDetaching([$training->id]);
        }

        return $role;
    }

    private function chain(BiicfJobRole ...$roles): void
    {
        for ($i = 0; $i < count($roles) - 1; $i++) {
            $roles[$i]->progressesTo()->syncWithoutDetaching([$roles[$i + 1]->id]);
        }
    }

    // ================= IT SERVICES =================
    private function seedItServices(BiicfSubSector $s): void
    {
        $tech = BiicfJobRole::updateOrCreate(['slug' => 'it-technician'], [
            'sub_sector_id' => $s->id, 'title' => 'IT Technician', 'functional_group' => 'Infrastructure / Hardware Support / Software and Systems',
            'job_description' => 'Provides support functions ranging from setting up technology equipment for employees to maintaining internal networks, supporting remote work, and providing help desk support. Maintains functioning IT equipment and networks, and upholds policies regarding use, security and confidentiality of data.',
            'critical_work_function' => "Install, maintain and upgrade desktop hardware and software\nInstall and test new ICT equipment and peripherals\nPerform basic PC hardware repairs and upgrades\nDiagnose and resolve basic PC, printer and software faults\nInstall and maintain standard network cabling\nRecord keeping of incidents and service requests",
            'alternative_titles' => ['Technology Associate', 'IT Generalist', 'Network Administrator', 'Network Support Technician', 'IT Administrator', 'Help Desk Specialist', 'IT Professional', 'IT Technical Support Professional', 'IT Support Engineer'],
            'career_path_level' => 0, 'box_colour' => 'primary',
        ]);
        $tech->competencies()->syncWithoutDetaching([
            $this->comp('Application Development')->id => ['proficiency_level_id' => $this->lvl(2)->id, 'is_core' => true],
            $this->comp('Infrastructure Management')->id => ['proficiency_level_id' => $this->lvl(2)->id, 'is_core' => true],
            $this->comp('Information Security Management')->id => ['proficiency_level_id' => $this->lvl(3)->id, 'is_core' => true],
            $this->comp('IT Project Management')->id => ['proficiency_level_id' => $this->lvl(2)->id, 'is_core' => true],
            $this->comp('Service Management')->id => ['proficiency_level_id' => $this->lvl(2)->id, 'is_core' => true],
            $this->comp('Communication')->id => ['proficiency_level_id' => $this->lvl(2)->id, 'is_core' => false],
            $this->comp('Teamwork')->id => ['proficiency_level_id' => $this->lvl(2)->id, 'is_core' => false],
            $this->comp('Analytical Thinking')->id => ['proficiency_level_id' => $this->lvl(2)->id, 'is_core' => false],
        ]);
        BiicfEntryRequirement::updateOrCreate(['job_role_id' => $tech->id], [
            'bdqf_level' => 'BDQF Level 2-4 (by seniority)',
            'field_of_study' => 'IT or related field',
            'alternative_pathway' => 'BDQF Level 3 with relevant industry certification can substitute for Level 4.',
            'years_experience' => 'Level 2 requires relevant industry experience alongside BDQF Level 2.',
        ]);
        foreach (['Cisco Certified Technician (CCT)', 'Cisco Certified Network Associate (CCNA)', 'CompTIA A+', 'CompTIA Network+', 'CompTIA Security+', 'Microsoft 365 Fundamentals', 'PMI Certified Associate in Project Management (CAPM)'] as $t) {
            $tr = BiicfTraining::updateOrCreate(['name' => $t], ['name' => $t]);
            $tech->trainings()->syncWithoutDetaching([$tr->id]);
        }

        $admin = $this->role($s, 'IT Administrator/Manager', 'it-administrator-manager', 'Hardware Support',
            'Plans, directs and coordinates activities in electronic data processing, information systems and computer programming. Procures and manages IT assets, and ensures the secure and effective operation of all computer systems used within the company.',
            "Manage IT staff, budget and cost-effectiveness\nMonitor daily operations across servers, hardware, software and OS\nCoordinate technology installations, upgrades and maintenance\nEvaluate technology risk and develop disaster recovery plans\nBuild long-term relationships with vendors",
            ['Systems Administrator', 'System Admin', 'Server Administrator', 'IT Systems Administrator', 'MIS Manager'], 1,
            ['Business Analysis' => 3, 'Budgeting' => 2, 'IT Asset Management' => 3, 'Performance Management' => 3, 'Problem Management' => 3, 'Process Improvement' => 3, 'Procurement' => 3, 'Service Level Management' => 3, 'Information Security Management' => 3, 'IT Project Management' => 3, 'Service Management' => 3],
            ['Analytical Thinking' => 3, 'Decision-Making' => 3, 'Communication' => 3, 'Work Management' => 3, 'Teamwork' => 3, 'People Management' => 3, 'Creativity and Innovation' => 3, 'Results Orientation' => 3, 'Service Orientation' => 3, 'Negotiation' => 3, 'Resilience' => 3],
            'BDQF Level 6', 'IT, Information Systems, Computer Science or related field',
            'BDQF Level 5 with relevant industry experience or portfolio also accepted.', null,
            ['Certified Information Systems Security Professional (CISSP)', 'Certified Information Security Manager (CISM)', 'Certified ScrumMaster (CSM)', 'Information Technology Infrastructure Library (ITIL)', 'Project Management Professional (PMP)', 'TOGAF 9']
        );

        $sysEng = $this->role($s, 'Systems Engineer', 'systems-engineer', 'Infrastructure / Hardware Support',
            'Monitors and manages all installed systems and infrastructure. Establishes, configures, tests and maintains operating systems, application software and system management tools. Evaluates existing systems and provides technical direction, and formulates security systems to maintain data safety.',
            "Plan, carry out and oversee IT system installation projects\nAssist with infrastructure testing and implementation\nConduct systems audits and upgrades\nAct as primary interface with equipment vendors\nMonitor compliance to security procedures and policies",
            ['Hardware Engineer', 'Infrastructure Engineer', 'Technical Engineer', 'IT Engineer'], 2,
            ['Business Analysis' => 3, 'IT Architecture' => 3, 'IT Asset Management' => 3, 'Cloud Computing' => 3, 'System Integration' => 4, 'Infrastructure Management' => 3, 'IT Project Management' => 2, 'Network Security Management' => 2, 'Information Security Management' => 3, 'Vendor Management' => 4, 'Service Management' => 2],
            ['Analytical Thinking' => 4, 'Decision-Making' => 4, 'Communication' => 4, 'Work Management' => 4, 'Teamwork' => 4, 'People Management' => 3, 'Creativity and Innovation' => 4, 'Results Orientation' => 4, 'Service Orientation' => 4, 'Negotiation' => 3, 'Resilience' => 4],
            'BDQF Level 6', 'IT, Computer Science, Software Engineering or related field',
            'BDQF Level 5 with relevant industry certification also accepted.', null,
            ['Certified Information Systems Security Professional - Architecture (CISSP)', 'Cisco Certified Network Associate (CCNA)', 'CompTIA Server+', 'Information Technology Infrastructure Library (ITIL) 4 Foundation', 'Microsoft Certified Systems Engineer (MCSE)', 'VMware Certified Professional (VCP) Data Centre Virtualization']
        );

        $coord = $this->role($s, 'Project Coordinator', 'project-coordinator', 'Programme Management',
            'Coordinates project implementation and assists the project manager to achieve project objectives. Facilitates project resources, manages progress, and applies knowledge in project planning, budgets and methodologies.',
            "Develop the project plan with detailed activities and cost estimates\nDrive project to meet schedule, budget and quality goals\nIdentify and resolve implementation problems\nSupervise team leadership and coaching",
            ['Project Assistant', 'Project Administrator', 'Project Scheduler', 'Project Planner'], 2,
            ['Business Analysis' => 3, 'Business Risk Management' => 3, 'Contract Management' => 3, 'Vendor Management' => 3, 'Procurement' => 3, 'IT Project Management' => 3, 'Information Security Management' => 2, 'Service Management' => 3],
            ['Analytical Thinking' => 3, 'Decision-Making' => 3, 'Communication' => 4, 'Work Management' => 3, 'Teamwork' => 4, 'People Management' => 3, 'Creativity and Innovation' => 3, 'Results Orientation' => 3, 'Service Orientation' => 4, 'Negotiation' => 3, 'Resilience' => 3],
            'BDQF Level 5', 'IT-related field',
            'Any relevant Project Management Certification with experience also accepted.', null,
            ['Certified Associate in Project Management (CAPM)', 'APM Project Fundamentals Qualification (PFQ)', 'CompTIA Project+', 'Prince2 Foundation', 'Project Management Professional (PMP)']
        );

        $pm = $this->role($s, 'Project Manager', 'project-manager', 'Programme Management',
            'Provides project planning and management for established initiatives, ensuring projects are completed to specification within time and budget. Serves as lead subject matter expert regarding technology concerns.',
            "Plan and define project objectives, milestones and deliverables\nDevelop estimates for cost, time, schedule and manpower\nManage project execution and vendor selection\nAdvise, defend and negotiate courses of action with stakeholders",
            ['Project Management Specialist'], 3,
            ['Business Analysis' => 4, 'Business Risk Management' => 4, 'Contract Management' => 4, 'Vendor Management' => 4, 'Procurement' => 4, 'IT Project Management' => 4, 'Information Security Management' => 4, 'Service Management' => 4],
            ['Analytical Thinking' => 4, 'Decision-Making' => 4, 'Communication' => 4, 'Work Management' => 4, 'Teamwork' => 4, 'People Management' => 4, 'Creativity and Innovation' => 4, 'Results Orientation' => 4, 'Service Orientation' => 4, 'Negotiation' => 4, 'Resilience' => 4],
            'BDQF Level 6', 'Business IT, Information Systems or related field',
            'BDQF Level 5 with Project Management Certification and experience also accepted.', null,
            ['Project Management Professional (PMP)', 'Prince2 Foundation', 'Master Project Manager (MPM)', 'Program Management Professional (PgMP)', 'CompTIA Project+']
        );

        // Career-path-only senior roles (no full detail page fetched; kept lighter)
        $itOpsManager = BiicfJobRole::updateOrCreate(['slug' => 'it-operations-manager'], [
            'sub_sector_id' => $s->id, 'title' => 'IT Operations Manager', 'functional_group' => 'Programme Management',
            'job_description' => 'Oversees IT operations across teams, ensuring service levels, budgets and strategic alignment with the business. Sits above Project Manager and Systems Engineer in the IT Services career path.',
            'critical_work_function' => "Set and monitor service-level objectives\nManage IT operations budget\nAlign IT operations with business strategy",
            'alternative_titles' => [], 'career_path_level' => 4, 'box_colour' => 'light-blue',
        ]);
        $cio = BiicfJobRole::updateOrCreate(['slug' => 'chief-information-officer'], [
            'sub_sector_id' => $s->id, 'title' => 'Chief Information Officer', 'functional_group' => 'Leadership',
            'job_description' => 'Sets organisation-wide technology strategy and leads digital transformation initiatives. Top of the IT Services career path.',
            'critical_work_function' => "Define enterprise IT and digital strategy\nManage IT governance and risk\nRepresent technology function at executive/board level",
            'alternative_titles' => ['CIO'], 'career_path_level' => 5, 'box_colour' => 'light-blue',
        ]);

        $this->chain($tech, $admin, $sysEng, $pm, $itOpsManager, $cio);
        $tech->progressesTo()->syncWithoutDetaching([$coord->id]);
        $coord->progressesTo()->syncWithoutDetaching([$pm->id]);
    }

    // ================= TELECOMMUNICATIONS AND NETWORK =================
    private function seedTelecom(BiicfSubSector $s): void
    {
        $techn = $this->role($s, 'Network Technician', 'network-technician', 'Telecommunications',
            'Supports the deployment and operations of network infrastructure. Performs installation, monitoring, troubleshooting, maintaining and testing of network systems and solutions, including fault repair and configuration at sites.',
            "Establish communications systems by installing/operating/maintaining telecom circuits and equipment\nPlan network installations from customer orders and technical specifications\nVerify service by testing circuits, equipment and alarms\nDocument network by labelling and recording configuration diagrams\nMaintain network by troubleshooting and repairing outages",
            ['Telecommunication Technician', 'Communication Technician', 'Engineering Technician'], 0,
            ['Business Analysis' => 2, 'Network Administration and Maintenance' => 3, 'Network Configuration' => 3, 'Test Planning' => 2, 'Infrastructure Support' => 3, 'Information Security Management' => 2, 'Fault Management' => 2, 'IT Project Management' => 2, 'Service Management' => 2, 'Telecommunications Network Management' => 3],
            ['Analytical Thinking' => 3, 'Decision-Making' => 3, 'Communication' => 3, 'Work Management' => 3, 'Teamwork' => 3, 'People Management' => 3, 'Creativity and Innovation' => 3, 'Results Orientation' => 3, 'Service Orientation' => 3, 'Negotiation' => 3, 'Resilience' => 3],
            'BDQF Level 4', 'IT, Telecommunications Network or related field',
            'BDQF Level 3 with relevant industry certification also accepted.', null,
            ['Master Certified Electronics Technician (CETma)', 'BICSI Technician', 'Cisco Certified Network Associate (CCNA)', 'Cisco Certified Network Professional (CCNP)', 'Cisco Certified Entry Networking Technician (CCENT)']
        );

        $lead = $this->role($s, 'Network Team Lead', 'network-team-lead', 'Software and Systems',
            'Leads the operations team to restore faults and proactively maintain network infrastructure for both fixed and wireless. Plans operational readiness for new projects, supports 24/7 operations, and manages technicians.',
            "Provide fixed and/or wireless network support: installation, configuration, testing, troubleshooting\nCarry out routine maintenance and generate fault reports\nRespond to user complaints and troubleshoot network issues\nMaintain SLA-related documentation",
            ['Network Controller', 'Network Consultant', 'Network Coordinator', 'Network Engineer'], 1,
            ['Business Analysis' => 3, 'IT Architecture' => 3, 'Business Risk Management' => 3, 'Infrastructure Management' => 4, 'Network Administration and Maintenance' => 3, 'Network Configuration' => 4, 'Emerging Technology Synthesis' => 4, 'Vendor Management' => 3, 'IT Project Management' => 3, 'Telecommunications Network Management' => 4, 'Network Security Management' => 4, 'Service Management' => 3, 'Fault Management' => 4],
            ['Analytical Thinking' => 4, 'Decision-Making' => 4, 'Communication' => 4, 'Work Management' => 4, 'Teamwork' => 4, 'People Management' => 4, 'Creativity and Innovation' => 4, 'Results Orientation' => 4, 'Service Orientation' => 4, 'Negotiation' => 4, 'Resilience' => 4],
            'BDQF Level 6', 'Information Systems, Computer Science or related field',
            'BDQF Level 5 with relevant industry experience/portfolio also accepted.', null,
            ['Cisco Certified Internetwork Expert (CCIE)', 'Cisco Certified Network Professional (CCNP)', 'Wireshark Certified Network Analyst (WCNA)', 'Microsoft Certified Solutions Expert (MCSE): Core Infrastructure', 'Huawei Certified Network Engineer (HCNE)']
        );

        $eng = $this->role($s, 'Network Engineer', 'network-engineer', 'Telecommunications',
            'Plans technical support, forecasting, design, project management, installation, monitoring and support maintenance of enterprise WAN/LAN/wireless networks. Performs network monitoring, performance tuning and troubleshooting to maintain network performance.',
            "Design cost-effective network systems complying with standards and best practices\nOversee installation, upgrading, operation and maintenance of LAN/WAN\nManage network infrastructure and disaster recovery planning\nPerform fault troubleshooting and root cause analysis\nReview compliance with information security policies",
            ['Network Administrator', 'Network Analyst', 'Core Network Engineer', 'Data Network Engineer', 'Telecommunications Network Engineer', 'Wireless Communication Network Engineer'], 2,
            ['Business Analysis' => 5, 'IT Architecture' => 4, 'Business Risk Management' => 5, 'Infrastructure Management' => 5, 'Network Administration and Maintenance' => 4, 'Network Configuration' => 4, 'Emerging Technology Synthesis' => 4, 'Vendor Management' => 4, 'IT Project Management' => 4, 'Telecommunications Network Management' => 5, 'Network Security Management' => 4, 'Service Management' => 4],
            ['Analytical Thinking' => 5, 'Decision-Making' => 5, 'Communication' => 5, 'Work Management' => 5, 'Teamwork' => 5, 'People Management' => 5, 'Creativity and Innovation' => 4, 'Results Orientation' => 5, 'Service Orientation' => 4, 'Negotiation' => 5, 'Resilience' => 5],
            'BDQF Level 5 (Specialist) or higher for Expert/Management', 'IT, Telecommunications or related field',
            'Expert/Management level requires BDQF Level 5 plus 3 years experience.', '3+ years for Expert/Management level',
            ['AWS Certified Advanced Networking', 'AWS Certified Solutions Architect - Associate', 'Cisco Certified Internetwork Expert (CCIE)', 'Google Certified Professional Cloud Architect', 'Information Technology Infrastructure Library (ITIL)']
        );

        $cto = BiicfJobRole::updateOrCreate(['slug' => 'chief-technology-officer-telecom'], [
            'sub_sector_id' => $s->id, 'title' => 'Chief Technology Officer', 'functional_group' => 'Leadership',
            'job_description' => 'Arranges and delivers presentations regarding telecommunications networks; oversees coordination of standardisation, ICT development plans and policy-related activities. Advocates the organisation\'s technological vision. Top of the Telecommunications and Network career path.',
            'critical_work_function' => "Lead vendor evaluation, selection and onboarding\nNegotiate and manage vendor contracts and SLAs\nOversee and optimise telecommunications systems and infrastructure",
            'alternative_titles' => ['CTO'], 'career_path_level' => 4, 'box_colour' => 'light-blue',
        ]);
        $infraManager = BiicfJobRole::updateOrCreate(['slug' => 'infrastructure-manager'], [
            'sub_sector_id' => $s->id, 'title' => 'Infrastructure Manager', 'functional_group' => 'Telecommunications',
            'job_description' => 'Manages telecommunications infrastructure programmes and teams, sitting between Network Team Lead/Network Engineer and CTO in the career path.',
            'critical_work_function' => "Oversee infrastructure engineering teams\nManage infrastructure budgets and vendor relationships",
            'alternative_titles' => [], 'career_path_level' => 3, 'box_colour' => 'primary',
        ]);
        $infraEngineer = BiicfJobRole::updateOrCreate(['slug' => 'infrastructure-engineer-telecom'], [
            'sub_sector_id' => $s->id, 'title' => 'Infrastructure Engineer', 'functional_group' => 'Telecommunications',
            'job_description' => 'Engineers telecommunications infrastructure solutions, an alternate track alongside Network Engineer in the career path.',
            'critical_work_function' => "Design and implement infrastructure solutions\nSupport infrastructure operations and upgrades",
            'alternative_titles' => [], 'career_path_level' => 2, 'box_colour' => 'primary',
        ]);

        $this->chain($techn, $lead, $eng, $infraManager, $cto);
        $lead->progressesTo()->syncWithoutDetaching([$infraEngineer->id]);
        $infraEngineer->progressesTo()->syncWithoutDetaching([$infraManager->id]);
    }

    // ================= APPLICATIONS AND SOLUTIONS DEVELOPMENT =================
    private function seedAppsDev(BiicfSubSector $s): void
    {
        $dev = $this->role($s, 'Applications Developer', 'applications-developer', 'Software and Systems',
            'Creates and tests software/applications in accordance with detailed technical design to ensure business requirements are met. Participates in review, analysis and verification of business and software requirements, and resolves software issues.',
            "Support stakeholder discussions to understand business needs\nIntegrate applications with databases from the back end\nApply bug-fixes and deploy applications per specifications\nCollect user feedback and generate performance reports\nFollow secure coding principles to avoid vulnerabilities",
            ['Application Engineer', 'Software Programmer', 'Programmer', 'Software Architect'], 0,
            ['Business Analysis' => 3, 'Application Development' => 4, 'Application Integration' => 3, 'Application Support and Enhancement' => 3, 'IT Architecture' => 3, 'Information Security Management' => 3, 'Security Architecture' => 3, 'Software Configuration' => 3, 'Software Design' => 3, 'Software Testing' => 3, 'User Interface Design' => 4, 'IT Project Management' => 3, 'Service Management' => 4],
            ['Analytical Thinking' => 4, 'Decision-Making' => 3, 'Communication' => 3, 'Work Management' => 3, 'Teamwork' => 3, 'People Management' => 3, 'Creativity and Innovation' => 4, 'Results Orientation' => 4, 'Service Orientation' => 4, 'Negotiation' => 3, 'Resilience' => 4],
            'BDQF Level 6 (with 3 years experience) or Level 5', 'Information Systems, Computer Science or related field',
            'BDQF Level 5 with 5 years relevant industry experience or portfolio also accepted.', null,
            ['.NET Core Development', 'Associate Android Developer (Google)', 'Developing ASP.Net Core MVC Web Applications', 'ITIL Foundation', 'Scrum Developer'],
        );

        $arch = $this->role($s, 'Solutions Architect', 'solutions-architect', 'Application Development',
            'Analyses, designs and develops roadmaps and implementation plans based on current versus future state business architecture. Leads and facilitates the software architecture governance process, and consults with clients and IT teams on architecture solutions.',
            "Formulate the organisation's architecture strategy, roadmap, standards and policies\nEvaluate client system specifications and enterprise architecture state\nAnalyse and develop software architectural requirements\nOversee guidelines and standards used in software development and integration\nResearch emerging technologies and industry trends",
            ['Software Architect', 'Technical Architect', 'Application Architect', 'Infrastructure Architect', 'IT Architect'], 1,
            ['Application Development' => 4, 'IT Architecture' => 4, 'Application Integration' => 4, 'Business Analysis' => 4, 'Quality Standards' => 4, 'Security Architecture' => 4, 'Software Configuration' => 4, 'Software Design' => 5, 'User Interface Design' => 4, 'Software Testing' => 4, 'Service Management' => 4],
            ['Analytical Thinking' => 4, 'Decision-Making' => 4, 'Communication' => 4, 'Work Management' => 4, 'Teamwork' => 4, 'People Management' => 3, 'Creativity and Innovation' => 4, 'Results Orientation' => 4, 'Service Orientation' => 4, 'Negotiation' => 3, 'Resilience' => 4],
            'BDQF Level 6', 'Computer Science, IT, Software Engineering or related field',
            'BDQF Level 5 with 6 years relevant industry experience also accepted.', null,
            ['Amazon Web Services (AWS)', 'AWS Certified Solutions Architect - Professional', 'Azure Solution Architect Certification', 'The Open Group Architecture Framework (TOGAF)', 'Google Cloud Architect Certification']
        );

        $junior = BiicfJobRole::updateOrCreate(['slug' => 'junior-programmer'], [
            'sub_sector_id' => $s->id, 'title' => 'Junior Programmer', 'functional_group' => 'Software and Systems',
            'job_description' => 'Entry point of the Applications and Solutions Development career path, writing and testing code under guidance before progressing to Applications Developer.',
            'critical_work_function' => "Write and test code from specifications\nSupport senior developers on assigned modules",
            'alternative_titles' => [], 'career_path_level' => 0, 'box_colour' => 'primary',
        ]);
        $softArch = BiicfJobRole::updateOrCreate(['slug' => 'software-architect-appsdev'], [
            'sub_sector_id' => $s->id, 'title' => 'Software Architect', 'functional_group' => 'Application Development',
            'job_description' => 'Parallel senior track to Solutions Architect focused specifically on software-level architecture decisions.',
            'critical_work_function' => "Define software architecture standards\nGuide development teams on architectural best practice",
            'alternative_titles' => [], 'career_path_level' => 1, 'box_colour' => 'primary',
        ]);
        $sysEngApp = BiicfJobRole::updateOrCreate(['slug' => 'systems-engineer-appsdev'], [
            'sub_sector_id' => $s->id, 'title' => 'Systems Engineer', 'functional_group' => 'Software and Systems',
            'job_description' => 'Systems engineering track within Applications and Solutions Development, bridging Applications Developer and Software Development Manager.',
            'critical_work_function' => "Support systems integration for application platforms\nMaintain deployment and CI/CD infrastructure",
            'alternative_titles' => [], 'career_path_level' => 2, 'box_colour' => 'primary',
        ]);
        $devManager = BiicfJobRole::updateOrCreate(['slug' => 'software-development-manager'], [
            'sub_sector_id' => $s->id, 'title' => 'Software Development Manager', 'functional_group' => 'Leadership',
            'job_description' => 'Manages development teams and delivery of software products, sitting below CTO in the career path.',
            'critical_work_function' => "Manage development team performance and delivery\nOwn sprint planning and release management",
            'alternative_titles' => [], 'career_path_level' => 3, 'box_colour' => 'light-blue',
        ]);
        $ctoApps = BiicfJobRole::updateOrCreate(['slug' => 'chief-technology-officer-appsdev'], [
            'sub_sector_id' => $s->id, 'title' => 'Chief Technology Officer', 'functional_group' => 'Leadership',
            'job_description' => 'Defines the organisation\'s cloud and technology strategy, ensuring secure, scalable use of infrastructure. Directs decisions on technology viability and leads significant technical initiatives. Top of the Applications and Solutions Development career path.',
            'critical_work_function' => "Define enterprise cloud and technology strategy\nMentor technical teams and foster innovation\nEstablish frameworks for evaluating innovation research",
            'alternative_titles' => ['Director of Technology', 'VP of Technology', 'Chief Digital Officer (CDO)', 'Chief Innovation Officer', 'Chief Technical Officer'],
            'career_path_level' => 4, 'box_colour' => 'light-blue',
        ]);

        $this->chain($junior, $dev, $sysEngApp, $devManager, $ctoApps);
        $dev->progressesTo()->syncWithoutDetaching([$arch->id]);
        $arch->progressesTo()->syncWithoutDetaching([$softArch->id]);
        $softArch->progressesTo()->syncWithoutDetaching([$devManager->id]);
    }

    // ================= ICT SECURITY =================
    private function seedIctSecurity(BiicfSubSector $s): void
    {
        $analyst = $this->role($s, 'Associate Security Analyst', 'associate-security-analyst', 'IT Security',
            'Ensures the company\'s digital assets are protected from unauthorised access. Secures online and on-premise infrastructures, monitors for suspicious activity, and helps mitigate risks before breaches occur.',
            "Perform cyber security monitoring activities on IT systems and applications\nAssist with vulnerability and penetration assessments\nAssist in forensic threat investigations\nEducate users on cyber security policies and standards",
            ['Security Consultant', 'Information Security Analyst', 'Security Operations Analyst', 'Information Security Officer'], 0,
            ['Business Analysis' => 3, 'Cyber and Data Breach Incident Management' => 3, 'Cyber Risk Management' => 3, 'Emerging Technology Synthesis' => 3, 'Infrastructure Design' => 3, 'Network Security Management' => 3, 'Security Architecture' => 3, 'Security Administration' => 3, 'Security Governance' => 3, 'Security Programme Management' => 3, 'Stakeholder Management' => 3, 'Security Implementation' => 3, 'Security Planning' => 3],
            ['Analytical Thinking' => 3, 'Decision-Making' => 3, 'Communication' => 4, 'Work Management' => 3, 'Teamwork' => 4, 'People Management' => 3, 'Creativity and Innovation' => 4, 'Results Orientation' => 4, 'Service Orientation' => 4, 'Negotiation' => 3, 'Resilience' => 4],
            'BDQF Level 5', 'Information Systems, Computer Science or related field',
            'BDQF Level 4 with 5 years relevant industry experience or portfolio also accepted.', null,
            ['CompTIA Infrastructure and Cybersecurity Certification', 'Certified Information Systems Auditor (CISA)', 'Security+', 'Certified Ethical Hacker (CEH)', 'GIAC Security Essentials Certification (GSEC)']
        );

        $risk = $this->role($s, 'Cyber Risk Analyst', 'cyber-risk-analyst', 'Software and Systems',
            'Monitors systems and evaluates threats that could breach the network. Conducts cyber risk assessment in support of technology initiatives, identifies IT-related risks, and determines appropriate controls to mitigate them.',
            "Conduct review of existing security policies, procedures and standards\nAssess third-party security controls and internal security systems\nDocument methodologies and tools to mitigate cyber risks\nRecommend corrective actions to mitigate technical risks",
            ['Risk Analyst', 'IT Risk Analyst', 'Risk Control Consultant', 'Risk Assessment Analyst', 'Information Security Analyst', 'Threat Analyst'], 1,
            ['Business Analysis' => 4, 'Cyber and Data Breach Incident Management' => 3, 'Cyber Risk Management' => 4, 'Emerging Technology Synthesis' => 3, 'Infrastructure Design' => 3, 'Network Security Management' => 4, 'Security Architecture' => 4, 'Security Administration' => 4, 'Security Governance' => 4, 'Security Programme Management' => 3, 'Stakeholder Management' => 3, 'Security Implementation' => 4, 'Security Planning' => 4],
            ['Analytical Thinking' => 4, 'Decision-Making' => 4, 'Communication' => 4, 'Work Management' => 4, 'Teamwork' => 4, 'People Management' => 3, 'Creativity and Innovation' => 4, 'Results Orientation' => 4, 'Service Orientation' => 4, 'Negotiation' => 3, 'Resilience' => 4],
            'BDQF Level 6', 'Information Systems, Computer Science or related field',
            'BDQF Level 5 with 4 years experience in relevant roles also accepted.', null,
            ['CompTIA Security+', 'CompTIA Cybersecurity Analyst', 'Certified Information Systems Security Professional', 'Certified Information Systems Auditor (CISA)', 'ISO Training (ISO27001)']
        );

        $secEng = $this->role($s, 'Security Engineer', 'security-engineer', 'Software and Systems',
            'Designs, develops and implements secure system architectures. Develops system security criteria, conducts security threat and vulnerability studies, and embeds security principles into enterprise system architecture design.',
            "Design security controls and systems in alignment with security guidelines\nImplement security system design via production and deployment planning\nMonitor security systems for strengths/weaknesses, proposing improvements\nOversee maintenance of security systems, platforms and associated software",
            ['Information Security Engineer', 'Cyber Security Engineer', 'Security Systems Engineer', 'IT Security Engineer', 'Protection Engineer'], 2,
            ['Business Analysis' => 4, 'Cyber and Data Breach Incident Management' => 4, 'Cyber Risk Management' => 4, 'Emerging Technology Synthesis' => 4, 'Infrastructure Design' => 5, 'Network Security Management' => 5, 'Security Architecture' => 5, 'Security Administration' => 4, 'Security Governance' => 4, 'Security Programme Management' => 4, 'Stakeholder Management' => 4, 'Security Implementation' => 4, 'Security Planning' => 4, 'Service Management' => 4],
            ['Analytical Thinking' => 4, 'Decision-Making' => 4, 'Communication' => 4, 'Work Management' => 4, 'Teamwork' => 4, 'People Management' => 3, 'Creativity and Innovation' => 4, 'Results Orientation' => 4, 'Service Orientation' => 4, 'Negotiation' => 3, 'Resilience' => 4],
            'BDQF Level 6', 'Cyber Security, IT Security Management, Information Security, Computer/System/Network Information Systems, Computer Science or related field',
            'BDQF Level 5 with 4-6 years, or BDQF Level 4 with 6-8 years relevant industry experience, also accepted.', '4-8 years depending on entry qualification',
            ['Certified Ethical Hacker (CEH)', 'Certified Information Security Manager (CISM)', 'Certified Information Systems Security Professional (CISSP)', 'CompTIA Advanced Security Practitioner (CASP+)', 'Offensive Security Certified Professional (OSCP)']
        );

        $cyberRiskManager = BiicfJobRole::updateOrCreate(['slug' => 'cyber-risk-manager'], [
            'sub_sector_id' => $s->id, 'title' => 'Cyber Risk Manager', 'functional_group' => 'Leadership',
            'job_description' => 'Manages the organisation\'s cyber risk programme, overseeing Cyber Risk Analysts and reporting into the CISO within the ICT Security career path.',
            'critical_work_function' => "Own the enterprise cyber risk register\nManage risk mitigation programmes across teams",
            'alternative_titles' => [], 'career_path_level' => 3, 'box_colour' => 'primary',
        ]);
        $biDirector = BiicfJobRole::updateOrCreate(['slug' => 'business-intelligence-director-security'], [
            'sub_sector_id' => $s->id, 'title' => 'Business Intelligence Director', 'functional_group' => 'Leadership',
            'job_description' => 'Senior leadership role bridging security engineering into enterprise intelligence and reporting, on the path toward CISO.',
            'critical_work_function' => "Direct enterprise security intelligence reporting\nAdvise executive leadership on risk posture",
            'alternative_titles' => ['Director Data Warehouse', 'Director Business Information', 'VP of Business Intelligence', 'Chief Business Intelligence Officer'],
            'career_path_level' => 3, 'box_colour' => 'primary',
        ]);
        $ciso = BiicfJobRole::updateOrCreate(['slug' => 'chief-information-security-officer'], [
            'sub_sector_id' => $s->id, 'title' => 'Chief Information Security Officer', 'functional_group' => 'Leadership',
            'job_description' => 'Sets organisation-wide security strategy and governance. Top of the ICT Security career path.',
            'critical_work_function' => "Define enterprise security strategy and governance\nRepresent security function at executive/board level",
            'alternative_titles' => ['CISO'], 'career_path_level' => 4, 'box_colour' => 'light-blue',
        ]);

        $this->chain($analyst, $secEng, $cyberRiskManager, $ciso);
        $analyst->progressesTo()->syncWithoutDetaching([$risk->id]);
        $risk->progressesTo()->syncWithoutDetaching([$biDirector->id]);
        $biDirector->progressesTo()->syncWithoutDetaching([$ciso->id]);
    }

    // ================= DIGITAL MEDIA =================
    private function seedDigitalMedia(BiicfSubSector $s): void
    {
        $market = $this->role($s, 'Market Analyst', 'market-analyst', 'Digital Media/Marketing',
            'Assists marketing teams by analysing marketing initiatives, reinforcing strategic numbers-driven decisions. Performs research and provides insights regarding market trends, competitors and existing customers.',
            "Provide the strategy behind a marketing campaign and analyse results\nConduct research on digital marketing trends\nAnalyse campaign data and educate marketers on how to use results\nCreate detailed reports on traffic and campaign costs",
            ['Marketing Assistant', 'Marketing Associate', 'Marketing Coordinator'], 0,
            ['Business Analysis' => 4, 'Customer Intelligence Analysis' => 4, 'Customer Behaviour Analysis' => 4, 'Data and Trend Analytics' => 4, 'Market Research' => 4, 'Media and Platform Management' => 4, 'Stakeholder Management' => 4, 'Budgeting' => 4, 'IT Project Management' => 3, 'Service Management' => 3],
            ['Analytical Thinking' => 4, 'Decision-Making' => 4, 'Communication' => 4, 'Work Management' => 4, 'Teamwork' => 4, 'People Management' => 3, 'Creativity and Innovation' => 4, 'Results Orientation' => 4, 'Service Orientation' => 4, 'Negotiation' => 3, 'Resilience' => 4],
            'BDQF Level 5 (with 5 years) or Level 4 (with 3 years)', 'Marketing, Communications, Economics or related field',
            null, '3-5 years depending on entry qualification',
            ['Digital Marketing Pro - Digital Marketing Institute', 'Google Analytics Certification', 'Fundamentals of Digital Marketing (Google)', 'HubSpot Content Marketing Certification', 'Marketing Analytics and Insights']
        );

        $dme = $this->role($s, 'Digital Marketing Executive', 'digital-marketing-executive', 'Digital Media/Marketing',
            'Plans, develops, implements and manages the overall digital marketing strategy. Manages and guides digital marketers, oversees digital marketing activities, and executes campaigns and marketing projects across channels.',
            "Contribute to web and digital marketing and communication planning\nMonitor results of web marketing and digital communications\nAnalyse effectiveness of campaigns and their impact on business outcomes\nDevise and manage market research, marketing planning and campaigns",
            ['Digital Marketing Assistant', 'Digital Marketing Coordinator', 'Digital Marketing Associate', 'Digital Marketing Analyst', 'Internet Marketing Specialist', 'Web Marketing Specialist', 'SEO Specialist', 'Paid Search Specialist', 'Social Media Specialist'], 1,
            ['Business Analysis' => 4, 'Customer Intelligence Analysis' => 4, 'Customer Behaviour Analysis' => 4, 'Data and Trend Analytics' => 4, 'Market Research' => 4, 'Media and Platform Management' => 4, 'Stakeholder Management' => 4, 'Budgeting' => 4, 'IT Project Management' => 3, 'Service Management' => 3],
            ['Analytical Thinking' => 4, 'Decision-Making' => 4, 'Communication' => 4, 'Work Management' => 4, 'Teamwork' => 4, 'People Management' => 3, 'Creativity and Innovation' => 4, 'Results Orientation' => 4, 'Service Orientation' => 4, 'Negotiation' => 3, 'Resilience' => 4],
            'BDQF Level 5', 'Marketing, Communications or related field',
            null, null,
            ['Google AdWords Certification', 'Meta Blueprint Certification', 'HubSpot Inbound Marketing Certification', 'Semrush SEO Toolkit Course', 'YouTube Certification']
        );

        $marketingManager = BiicfJobRole::updateOrCreate(['slug' => 'marketing-manager'], [
            'sub_sector_id' => $s->id, 'title' => 'Marketing Manager', 'functional_group' => 'Leadership',
            'job_description' => 'Manages the marketing function and team, sitting between Digital Marketing Executive/Business Development Manager and CMO in the career path.',
            'critical_work_function' => "Own the marketing budget and team performance\nSet marketing strategy across channels",
            'alternative_titles' => [], 'career_path_level' => 2, 'box_colour' => 'primary',
        ]);
        $bizDevManager = BiicfJobRole::updateOrCreate(['slug' => 'business-development-manager'], [
            'sub_sector_id' => $s->id, 'title' => 'Business Development Manager', 'functional_group' => 'Distribution',
            'job_description' => 'Drives new business opportunities and partnerships, an alternate track alongside Market Analyst toward Marketing Manager.',
            'critical_work_function' => "Identify and pursue new business opportunities\nManage key partner relationships",
            'alternative_titles' => [], 'career_path_level' => 1, 'box_colour' => 'primary',
        ]);
        $cxManager = BiicfJobRole::updateOrCreate(['slug' => 'customer-experience-manager'], [
            'sub_sector_id' => $s->id, 'title' => 'Customer Experience Manager', 'functional_group' => 'Distribution',
            'job_description' => 'Owns customer experience strategy across digital touchpoints, an alternate track alongside Digital Marketing Executive toward Marketing Manager.',
            'critical_work_function' => "Design and monitor customer experience journeys\nCoordinate cross-functional CX improvement initiatives",
            'alternative_titles' => [], 'career_path_level' => 1, 'box_colour' => 'primary',
        ]);
        $cmo = BiicfJobRole::updateOrCreate(['slug' => 'chief-marketing-officer'], [
            'sub_sector_id' => $s->id, 'title' => 'Chief Marketing Officer', 'functional_group' => 'Leadership',
            'job_description' => 'Sets organisation-wide marketing and brand strategy. Top of the Digital Media career path.',
            'critical_work_function' => "Define enterprise marketing and brand strategy\nRepresent marketing function at executive/board level",
            'alternative_titles' => ['CMO'], 'career_path_level' => 3, 'box_colour' => 'light-blue',
        ]);

        $this->chain($market, $bizDevManager, $marketingManager, $cmo);
        $dme->progressesTo()->syncWithoutDetaching([$cxManager->id]);
        $cxManager->progressesTo()->syncWithoutDetaching([$marketingManager->id]);
    }

    // ================= DATA AND ARTIFICIAL INTELLIGENCE =================
    private function seedDataAi(BiicfSubSector $s): void
    {
        $entry = BiicfJobRole::updateOrCreate(['slug' => 'data-entry'], [
            'sub_sector_id' => $s->id, 'title' => 'Data Entry', 'functional_group' => 'Data Operations',
            'job_description' => 'Enters, verifies and maintains data records within organisational systems, forming the entry point of the Data and Artificial Intelligence career path.',
            'critical_work_function' => "Input and update data records accurately\nVerify data quality and flag discrepancies\nMaintain basic documentation of data sources",
            'alternative_titles' => ['Data Entry Clerk', 'Data Entry Operator'], 'career_path_level' => 0, 'box_colour' => 'primary',
        ]);
        $entry->competencies()->syncWithoutDetaching([
            $this->comp('Database Management')->id => ['proficiency_level_id' => $this->lvl(2)->id, 'is_core' => true],
            $this->comp('Data Analytics')->id => ['proficiency_level_id' => $this->lvl(1)->id, 'is_core' => true],
            $this->comp('Communication')->id => ['proficiency_level_id' => $this->lvl(2)->id, 'is_core' => false],
            $this->comp('Work Management')->id => ['proficiency_level_id' => $this->lvl(2)->id, 'is_core' => false],
        ]);
        BiicfEntryRequirement::updateOrCreate(['job_role_id' => $entry->id], [
            'bdqf_level' => 'BDQF Level 3', 'field_of_study' => 'IT or related field', 'alternative_pathway' => null, 'years_experience' => null,
        ]);

        $analyst = $this->role($s, 'Data Analyst', 'data-analyst', 'Data Operations',
            'Collects, processes and performs statistical analysis on data. Interprets data to identify trends and patterns, and prepares reports and visualisations that support business decision-making.',
            "Collect and clean data from multiple sources\nPerform statistical analysis and build reports/dashboards\nCommunicate findings to non-technical stakeholders\nSupport ad hoc data requests from business teams",
            ['Business Intelligence Analyst', 'Reporting Analyst'], 1,
            ['Data Analytics' => 3, 'Database Management' => 3, 'Business Analysis' => 3, 'IT Project Management' => 2, 'Service Management' => 2],
            ['Analytical Thinking' => 3, 'Decision-Making' => 3, 'Communication' => 3, 'Work Management' => 3, 'Teamwork' => 3, 'Results Orientation' => 3, 'Resilience' => 3],
            'BDQF Level 6', 'IT, Computer Science, Mathematics, Statistics or related field',
            'BDQF Level 5 with relevant industry experience also accepted.', null,
            ['Google Data Analytics Certificate', 'Microsoft Certified: Data Analyst Associate', 'Tableau Desktop Specialist']
        );

        $engineer = $this->role($s, 'Data Engineer', 'data-engineer', 'Data Operations',
            'Designs, builds and maintains data pipelines and infrastructure for collecting, storing and processing large volumes of data, enabling analytics and machine learning use cases across the organisation.',
            "Design and build ETL/ELT data pipelines\nMaintain data warehouses and data lake infrastructure\nEnsure data quality, governance and security\nCollaborate with data scientists and analysts on data availability",
            ['Data Pipeline Engineer', 'ETL Developer'], 2,
            ['Database Management' => 4, 'Data Analytics' => 3, 'Cloud Computing' => 3, 'IT Architecture' => 3, 'Information Security Management' => 3, 'IT Project Management' => 2],
            ['Analytical Thinking' => 4, 'Decision-Making' => 3, 'Communication' => 3, 'Work Management' => 3, 'Teamwork' => 3, 'Results Orientation' => 4, 'Resilience' => 3],
            'BDQF Level 6', 'IT, Computer Science, Software Engineering or related field',
            'BDQF Level 5 with relevant industry experience also accepted.', null,
            ['Developer Training for Spark and Hadoop', 'Microsoft Azure Fundamental', 'AWS Certified Data Analytics', 'Oracle Database Administrator (DBA)']
        );

        $manager = $this->role($s, 'Data Manager', 'data-manager', 'Leadership',
            'Manages data governance, data quality and the data team\'s operations, ensuring data assets are managed as strategic organisational resources in line with policy and compliance requirements.',
            "Own data governance policies and data quality standards\nManage the data engineering/analytics team\nCoordinate with compliance on data protection and privacy\nReport on data infrastructure health and roadmap to leadership",
            ['Data Governance Manager', 'Database Manager'], 3,
            ['Database Management' => 4, 'Business Analysis' => 4, 'IT Project Management' => 4, 'Information Security Management' => 4, 'Stakeholder Management' => 4],
            ['Analytical Thinking' => 4, 'Decision-Making' => 4, 'Communication' => 4, 'Work Management' => 4, 'Teamwork' => 4, 'People Management' => 4, 'Results Orientation' => 4, 'Resilience' => 4],
            'BDQF Level 6', 'IT, Computer Science, Information Systems or related field',
            'BDQF Level 5 with several years relevant management experience also accepted.', null,
            ['Certified Data Management Professional (CDMP)', 'DAMA Certified Data Management Professional', 'ITIL Foundation']
        );

        $scientist = $this->role($s, 'Data Scientist', 'data-scientist', 'Data Operations',
            'Autonomously identifies and pursues research with significant business impact. Plans and leads development of new and advanced data analytics techniques, methodologies and analytical solutions from design through prototyping and testing.',
            "Plan and lead development of advanced data analytics techniques and models\nPrioritise and execute in the face of ambiguity, adapting tools to complex questions\nCollaborate with data science, analytics, engineering and economics disciplines\nDevelop reliable, reproducible analyses at scale",
            ['Data Mining Engineer', 'Machine Learning Engineer', 'Data Architect', 'Hadoop Engineer', 'Data Warehouse Architect'], 4,
            ['Data Analytics' => 5, 'Database Management' => 4, 'Business Analysis' => 4, 'Cloud Computing' => 3, 'Emerging Technology Synthesis' => 4],
            ['Analytical Thinking' => 5, 'Decision-Making' => 4, 'Communication' => 4, 'Work Management' => 4, 'Teamwork' => 4, 'Creativity and Innovation' => 4, 'Results Orientation' => 4, 'Resilience' => 4],
            'BDQF Level 6 (6+ years) or Level 5 (10+ years)', 'IT, Computer Science, Mathematics, Statistics, Management Information Systems, Software Engineering or related field',
            'Both pathways require industry certification alongside the stated experience.', '6-10 years depending on entry qualification',
            ['Data Science Professional Certification', 'Graduate Certificate in Data Science (Applied)', 'Deep Learning Specialization Certification - Coursera']
        );

        $biDirector = BiicfJobRole::updateOrCreate(['slug' => 'business-intelligence-director-data'], [
            'sub_sector_id' => $s->id, 'title' => 'Business Intelligence Director', 'functional_group' => 'Leadership',
            'job_description' => 'Directs enterprise business intelligence and data strategy, overseeing Data Managers and Data Scientists. Top of the Data and Artificial Intelligence career path.',
            'critical_work_function' => "Define enterprise data and BI strategy\nRepresent data function at executive/board level\nOversee data-driven decision-making culture",
            'alternative_titles' => ['Director Data Warehouse', 'Director Business Information', 'Director Business Info', 'Director Business Analytics', 'VP of Business Intelligence', 'Chief Business Intelligence Officer'],
            'career_path_level' => 5, 'box_colour' => 'light-blue',
        ]);

        $this->chain($entry, $analyst, $engineer, $manager, $biDirector);
        $engineer->progressesTo()->syncWithoutDetaching([$scientist->id]);
        $scientist->progressesTo()->syncWithoutDetaching([$biDirector->id]);
    }
}