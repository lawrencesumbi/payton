<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payton | Smart Financial Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        brand: {
                            light: '#9357f5',
                            dark: '#7a1fa2',
                            bg: '#f8fafc'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom scrollbar hide */
        html::-webkit-scrollbar, 
        body::-webkit-scrollbar {
            display: none;
            width: 0 !important;
            height: 0 !important;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        html, body {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;     /* Firefox */
            overflow-y: scroll;        /* Optional: Keeps scrolling functional */
        }

        html {
            scroll-snap-type: y proximity; /* Makes sections 'stick' when you get close */
            scroll-behavior: smooth;
        }

        section {
            scroll-snap-align: start; /* Aligns the top of the section to the top of the screen */
        }
        
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .dark .glass {
            background: rgba(15, 23, 42, 0.8);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .animate-float { animation: float 5s ease-in-out infinite; }

        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s ease-out; }
        .reveal.active { opacity: 1; transform: translateY(0); }

        @keyframes slideUpFade {
            0% {
                opacity: 0;
                transform: translateY(30px); /* Starts 30px lower */
            }
            100% {
                opacity: 1;
                transform: translateY(0);    /* Moves to original position */
            }
        } 

        .animate-slide-up {
            animation: slideUpFade 1s ease-out forwards;
            /* 'forwards' ensures the text stays visible after the animation ends */
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors duration-300">

    <nav id="navbar" class="fixed w-full z-50 top-0 transition-all duration-300 px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center glass rounded-2xl px-6 py-3 border border-white/20 shadow-lg">
            <div class="flex items-center gap-3">
                <img src="img/logo.jpg" alt="logo" class="w-10 h-10 rounded-xl shadow-md">
                <h3 class="text-2xl font-extrabold tracking-tighter uppercase italic">
                    <span class="text-brand-light">Pay</span><span class="text-brand-dark dark:text-purple-400">ton</span>
                </h3>
            </div>
            
            <ul class="hidden md:flex gap-8 font-semibold text-sm uppercase tracking-wider">
                <li><a href="#home" class="hover:text-brand-light transition">Home</a></li>
                <li><a href="#services" class="hover:text-brand-light transition">Features</a></li>
                <li><a href="#about" class="hover:text-brand-light transition">About</a></li>
                <li><a href="#contact" class="hover:text-brand-light transition">Team</a></li>
            </ul>

            <div class="flex items-center gap-4">
                <button id="theme-toggle" class="p-2 rounded-xl bg-slate-200/50 dark:bg-slate-800/50 hover:scale-110 transition">
                    <i id="theme-icon" data-lucide="moon" class="w-5 h-5 text-brand-dark dark:text-brand-light"></i>
                </button>
                <a href="login.php" class="bg-brand-dark hover:bg-brand-light text-white px-6 py-2.5 rounded-xl font-bold text-sm transition shadow-lg shadow-purple-500/20">
                    GET STARTED
                </a>
            </div>
        </div>
    </nav>

    <section id="home" class="min-h-screen flex items-center px-6 pt-20">
        <div class="max-w-7xl mx-auto grid md:grid-cols-[1.2fr_0.8fr] gap-1 items-center">
            
            <div class="space-y-8 md:pr-4">
                <div class="inline-block px-4 py-1.5 rounded-full bg-purple-100 dark:bg-purple-900/30 text-brand-dark dark:text-purple-300 text-xs font-bold tracking-widest uppercase">
                    AI-Assisted Finance Management
                </div>
                
                <h1 class="text-5xl md:text-7xl font-extrabold leading-[1.1] animate-slide-up">
                    The Future of Personal Finance, here with <span class="text-brand-light">Payton</span>.
                </h1>
                
                <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed min-h-[40px]">
                    <span id="typing-text"></span>
                </p>
                
                <div class="flex flex-wrap gap-4">
                    <a href="login.php" class="px-8 py-4 bg-brand-dark text-white rounded-2xl font-bold hover:scale-105 transition shadow-xl shadow-purple-500/20">Start Tracking Now</a>
                    <a href="#contact" class="px-8 py-4 border border-slate-300 dark:border-slate-700 rounded-2xl font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition">Contact Us</a>
                </div>
            </div>

            <div class="relative flex justify-center animate-float">
                <div class="absolute inset-0 bg-brand-light/20 blur-[100px] rounded-full"></div>
                <img src="img/download2.png" alt="Hero" class="relative z-10 w-full max-w-[520px] rounded-[2.5rem] shadow-2xl rotate-2 hover:rotate-0 transition-transform duration-500">
            </div>
        </div>
    </section>

    <section id="services" class="min-h-screen flex items-center px-6 bg-white dark:bg-slate-900/30 reveal">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 space-y-4">
                <h2 class="text-4xl font-extrabold">Powerful Features</h2>
                <p class="text-slate-500 max-w-2xl mx-auto">Intelligent tools to simplify your financial life.</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php
                $features = [
                    ['icon' => 'split', 'title' => 'Split Expense', 'desc' => 'Split bills with friends and manage shared recurring costs.', 'img' => 'img/services1.jpg'],
                    ['icon' => 'bar-chart-3', 'title' => 'Expense Tracking', 'desc' => 'Log spendings by categories and visualize your insights.', 'img' => 'img/services2.jpg'],
                    ['icon' => 'credit-card', 'title' => 'Payment Status', 'desc' => 'Track paid, unpaid, or overdue payments effortlessly.', 'img' => 'img/services3.jpg'],
                    ['icon' => 'bot', 'title' => 'AI-Assisted', 'desc' => 'Smart reminders and predictive insights for your budget.', 'img' => 'img/services4.jpg'],
                ];
                foreach ($features as $f): ?>
                <div class="group p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:border-brand-light transition duration-500">
                    <div class="w-12 h-12 mb-6 flex items-center justify-center rounded-2xl bg-brand-light text-white shadow-lg shadow-purple-500/30 group-hover:rotate-12 transition">
                        <i data-lucide="<?= $f['icon'] ?>"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3"><?= $f['title'] ?></h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6"><?= $f['desc'] ?></p>
                    <img src="<?= $f['img'] ?>" class="rounded-xl w-full h-40 object-cover shadow-md hover:scale-105 transition duration-500">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="about" class="min-h-screen flex items-center px-6 reveal">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div class="p-6 rounded-3xl bg-brand-dark text-white shadow-xl">
                        <i data-lucide="code-2" class="mb-4"></i>
                        <h4 class="font-bold">Backend</h4>
                        <p class="text-xs opacity-80">PHP & SQL for secure data handling.</p>
                    </div>
                    <div class="p-6 rounded-3xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        <i data-lucide="layers" class="mb-4 text-brand-light"></i>
                        <h4 class="font-bold">Frontend</h4>
                        <p class="text-xs text-slate-500">Modern HTML, CSS, Tailwind & JS.</p>
                    </div>
                </div>
                <div class="space-y-4 mt-8">
                    <div class="p-6 rounded-3xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        <i data-lucide="line-chart" class="mb-4 text-brand-light"></i>
                        <h4 class="font-bold">Tracker</h4>
                        <p class="text-xs text-slate-500">Monitor transactions effortlessly.</p>
                    </div>
                    <div class="p-6 rounded-3xl bg-brand-light text-white shadow-xl">
                        <i data-lucide="users-2" class="mb-4"></i>
                        <h4 class="font-bold">Integration</h4>
                        <p class="text-xs opacity-80">Splitwise-inspired bill sharing.</p>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <h2 class="text-4xl font-extrabold uppercase">What is <span class="text-brand-light">Payton</span>?</h2>
                <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                    We are more than a tracker. Payton is an intelligent financial ecosystem that provides actionable recommendations to help you make smarter financial decisions.
                </p>
                <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                    Unlike traditional tools, Payton integrates all your financial data in one secure platform, ensuring organization, accuracy, and convenience.
                </p>
            </div>
        </div>
    </section>

    <section id="contact" class="min-h-screen flex items-center py-24 px-6 bg-slate-100 dark:bg-slate-900/50 reveal">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-extrabold mb-4 uppercase">Meet the Team</h2>
                <p class="text-slate-500">The developers behind the Payton Financial System.</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8 mb-16">
                <?php
                $team = [
                    ['name' => 'Lawrence Guian Sumbi', 'role' => 'Backend Developer', 'img' => 'img/team1.jpg'],
                    ['name' => 'Patricia Ann Mae Obaob', 'role' => 'Frontend Developer', 'img' => 'img/team2.jpg'],
                    ['name' => 'Kris Jaylon G. Mantillas', 'role' => 'Project Manager', 'img' => 'img/team3.jpg'],
                    ['name' => 'Jaymaica J. Narvasa', 'role' => 'Analyst', 'img' => 'img/team4.jpg'],
                    ['name' => 'Dranreb Misa', 'role' => 'Documentor', 'img' => 'img/team5.jpg'],
                ];
                foreach ($team as $m): ?>
                <div class="text-center group">
                    <div class="relative inline-block mb-4">
                        <div class="absolute inset-0 bg-brand-light rounded-full blur-lg opacity-0 group-hover:opacity-40 transition duration-500"></div>
                        
                        <img src="<?= $m['img'] ?>" alt="<?= $m['name'] ?>" 
                            class="relative w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white dark:border-slate-800 shadow-xl object-cover group-hover:scale-110 transition duration-500">
                    </div>
                    <h4 class="font-bold text-sm md:text-base"><?= $m['name'] ?></h4>
                    <p class="text-xs text-brand-dark dark:text-brand-light font-semibold uppercase tracking-tighter"><?= $m['role'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="max-w-3xl mx-auto p-8 rounded-[2.5rem] bg-brand-dark text-white flex flex-col md:flex-row justify-between items-center gap-6 shadow-2xl transition-transform hover:scale-[1.02]">
                <div class="flex items-center gap-4"><i data-lucide="mail"></i> <span>payton@gmail.com</span></div>
                <div class="flex items-center gap-4"><i data-lucide="phone"></i> <span>+63 975 314 0724</span></div>
                <div class="flex items-center gap-4"><i data-lucide="map-pin"></i> <span>Cebu, PH</span></div>
            </div>
        </div>
    </section>

    <footer class="bg-slate-950 text-slate-400 py-16 px-6">
        <div class="max-w-7xl mx-auto grid md:grid-cols-3 gap-12 border-b border-slate-800 pb-12">
            <div>
                <h3 class="text-2xl font-bold text-white mb-4 uppercase tracking-tighter italic">PAY<span class="text-brand-light">TON</span></h3>
                <p class="text-sm leading-relaxed">AI-assisted financial management system for smarter tracking, expense, and insights.</p>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Quick Links</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#home" class="hover:text-white transition">Home</a></li>
                    <li><a href="#about" class="hover:text-white transition">About</a></li>
                    <li><a href="#services" class="hover:text-white transition">Features</a></li>
                </ul>
            </div>
            <div class="flex flex-col gap-4">
                <h4 class="text-white font-bold mb-4">Follow Us</h4>
                <div class="flex gap-4">
                    <a href="https://www.facebook.com/lawrence.sumbi.2024/" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-brand-light transition duration-300 text-white shadow-lg">
                        <i class="fab fa-facebook-f text-lg"></i>
                    </a>
                    
                    <a href="https://mail.google.com/mail/u/1/#inbox?compose=DmwnWsLWPlCZdcDjcPnhzLnHLMsPfKsrwvskHjZNRrlWQghrFfPKSMgWwQxzqKHhzCLDTqtlTxLG" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-brand-light transition duration-300 text-white shadow-lg">
                        <i class="fas fa-envelope text-lg"></i>
                    </a>
                    
                    <a href="https://www.youtube.com/@sumbilawrenceguianp.9254" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-brand-light transition duration-300 text-white shadow-lg">
                        <i class="fab fa-youtube text-lg"></i>
                    </a>
                </div>
            </div>
                </div>
        <div class="text-center pt-8 text-xs font-semibold tracking-widest uppercase">
            © <?= date('Y') ?> Payton. All Rights Reserved.
        </div>
    </footer>

<script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>

    <script>
        lucide.createIcons();

        // Theme Toggle Logic
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const html = document.documentElement;

        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
            themeIcon.setAttribute('data-lucide', 'sun');
        }

        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            const isDark = html.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            themeIcon.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
            lucide.createIcons();
        });

        // Scroll Reveal
        function reveal() {
            var reveals = document.querySelectorAll(".reveal");
            reveals.forEach(r => {
                var windowHeight = window.innerHeight;
                var elementTop = r.getBoundingClientRect().top;
                if (elementTop < windowHeight - 100) r.classList.add("active");
            });
        }
        window.addEventListener("scroll", reveal);
        reveal();

        // Navbar Scroll Effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if(window.scrollY > 50) {
                nav.classList.add('py-2');
                nav.querySelector('.glass').classList.add('shadow-xl');
            } else {
                nav.classList.remove('py-2');
                nav.querySelector('.glass').classList.remove('shadow-xl');
            }
        });

      var typed = new Typed('#typing-text', {
          strings: [
              'Stop guessing where your money goes.', 
              'Payton uses AI-assisted insights to monitor your expenses and automate bill reminders.', 
              'Experience a smarter way to split costs and manage your financial future effortlessly.'
          ],
          typeSpeed: 40,    // Speed in milliseconds
          backSpeed: 20,    // How fast it erases
          backDelay: 2000,  // How long it stays visible before erasing
          loop: true,       // Keeps the animation going
          showCursor: true,
          cursorChar: '|',
        });

    </script>
</body>
</html>