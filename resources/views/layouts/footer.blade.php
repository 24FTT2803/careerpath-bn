<footer class="bg-blue-900 text-white mt-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Logo & About -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-compass text-blue-900 text-xl"></i>
                    </div>
                    <span class="text-xl font-bold">CareerPath <span class="text-amber-400">BN</span></span>
                </div>
                <p class="text-gray-300 text-sm leading-relaxed">
                    AI-powered career guidance platform aligned with the Brunei ICT Industry Competency Framework (BIICF).
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="font-semibold text-white mb-4">Quick Links</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('student.dashboard') }}" class="text-gray-300 hover:text-amber-400 transition">Dashboard</a></li>
                    <li><a href="{{ route('student.profile') }}" class="text-gray-300 hover:text-amber-400 transition">Profile</a></li>
                    <li><a href="{{ route('student.milestones') }}" class="text-gray-300 hover:text-amber-400 transition">Milestones</a></li>
                    <li><a href="#" class="text-gray-300 hover:text-amber-400 transition">About BIICF</a></li>
                </ul>
            </div>

            <!-- Resources -->
            <div>
                <h4 class="font-semibold text-white mb-4">Resources</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="text-gray-300 hover:text-amber-400 transition">BIICF Framework</a></li>
                    <li><a href="#" class="text-gray-300 hover:text-amber-400 transition">ICT Sub-Sectors</a></li>
                    <li><a href="#" class="text-gray-300 hover:text-amber-400 transition">Competencies</a></li>
                    <li><a href="#" class="text-gray-300 hover:text-amber-400 transition">Career Guide</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="font-semibold text-white mb-4">Contact</h4>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><i class="fas fa-university w-5 text-amber-400"></i> Politeknik Brunei</li>
                    <li><i class="fas fa-map-marker-alt w-5 text-amber-400"></i> Jalan Ong Sum Ping, BSB</li>
                    <li><i class="fas fa-envelope w-5 text-amber-400"></i> sict@pb.edu.bn</li>
                    <li><i class="fas fa-phone w-5 text-amber-400"></i> +673 123 4567</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-blue-800 mt-8 pt-6 text-center text-sm text-gray-400">
            <p>&copy; {{ date('Y') }} CareerPath BN. Developed by SICT Students, Politeknik Brunei.</p>
            <p class="mt-1 text-xs">In collaboration with AITI - Brunei ICT Industry Competency Framework (BIICF)</p>
        </div>
    </div>
</footer>