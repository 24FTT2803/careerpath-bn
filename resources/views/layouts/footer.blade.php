<footer class="bg-blue-900 text-white mt-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Logo & About -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-compass text-blue-900 text-xl"></i>
                    </div>
                    <span class="text-xl font-bold">CareerPath <span class="text-amber-400">BN</span></span>
                </div>
                <p class="text-gray-300 text-sm leading-relaxed">
                    AI-powered career guidance platform aligned with the 
                    Brunei ICT Industry Competency Framework (BIICF).
                </p>
                <!-- Logos Section -->
                <div class="flex items-center gap-4 mt-4">
                    <!-- Politeknik Brunei Logo -->
                    <a href="https://www.pb.edu.bn" target="_blank" class="inline-block">
                        <img src="{{ asset('images/politeknik-logo.png') }}" 
                             alt="Politeknik Brunei" 
                             class="h-12 w-auto hover:opacity-80 transition-opacity"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <!-- Fallback if image doesn't load -->
                        <div class="hidden items-center gap-2 bg-white/10 px-3 py-2 rounded-lg">
                            <i class="fas fa-university text-amber-400 text-xl"></i>
                            <span class="text-white text-sm font-semibold">Politeknik Brunei</span>
                        </div>
                    </a>
                    <!-- BIICF Logo -->
                    <a href="#" target="_blank" class="inline-block">
                        <img src="{{ asset('images/biicf-logo.png') }}" 
                             alt="BIICF" 
                             class="h-12 w-auto hover:opacity-80 transition-opacity"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <!-- Fallback if image doesn't load -->
                        <div class="hidden items-center gap-2 bg-white/10 px-3 py-2 rounded-lg">
                            <i class="fas fa-certificate text-amber-400 text-xl"></i>
                            <span class="text-white text-sm font-semibold">BIICF</span>
                        </div>
                    </a>
                </div>
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
                    <li class="flex items-start gap-2">
                        <i class="fas fa-university w-5 text-amber-400 mt-0.5"></i>
                        <span>Politeknik Brunei</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-map-marker-alt w-5 text-amber-400 mt-0.5"></i>
                        <span>Jalan Ong Sum Ping, BSB</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-envelope w-5 text-amber-400 mt-0.5"></i>
                        <span>sict@pb.edu.bn</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-phone w-5 text-amber-400 mt-0.5"></i>
                        <span>+673 123 4567</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-blue-800 mt-8 pt-6 text-center text-sm text-gray-400">
            <p>&copy; {{ date('Y') }} CareerPath BN. Developed by SICT Students, Politeknik Brunei.</p>
            <p class="mt-1 text-xs">In collaboration with AITI - Brunei ICT Industry Competency Framework (BIICF)</p>
        </div>
    </div>
</footer>