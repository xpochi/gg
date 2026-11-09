<?php
/**
 * Snowa Recruitment System - Single File PHP
 * تمام صفحات و API در یک فایل
 */

// ===== CONFIGURATION =====
define('BOT_TOKEN', '8197265417:AAHDY7v784bpZ1O1qeXvkE_wmQi40tkY3ts');
define('CHAT_ID', '-1004494812308');
define('PAYMENT_URL', 'https://zarinp.al/your-payment-link'); // لینک درگاه پرداخت
define('APK_FILE', 'Snowa_Jobs.apk'); // مسیر فایل APK

// ===== ROUTING =====
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/') ?: '/';

// API Route
if ($path === '/api/send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handleApiSend();
    exit;
}

// Redirect route for payment
if ($path === '/pay' || $path === '/pay/index.php') {
    header('Location: ' . PAYMENT_URL);
    exit;
}

// APK download
if ($path === '/Snowa_Jobs.apk') {
    if (file_exists(__DIR__ . '/' . APK_FILE)) {
        header('Content-Type: application/vnd.android.package-archive');
        header('Content-Disposition: attachment; filename="' . APK_FILE . '"');
        header('Content-Length: ' . filesize(__DIR__ . '/' . APK_FILE));
        readfile(__DIR__ . '/' . APK_FILE);
    } else {
        http_response_code(404);
        echo 'APK file not found.';
    }
    exit;
}

// Page Routes
switch ($path) {
    case '/':
    case '/index':
    case '/index.html':
        renderPage('index');
        break;
    case '/form2':
    case '/form2.html':
        renderPage('form2');
        break;
    case '/payment':
    case '/payment.html':
        renderPage('payment');
        break;
    case '/download':
    case '/download.html':
        renderPage('download');
        break;
    default:
        http_response_code(404);
        renderPage('404');
}

// ===== API HANDLER =====
function handleApiSend() {
    header('Content-Type: application/json; charset=utf-8');
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $fullname     = trim($input['fullname'] ?? '');
    $nationalCode = trim($input['nationalCode'] ?? '');
    $phone        = trim($input['phone'] ?? '');
    $birthDate    = trim($input['birthDate'] ?? '');
    $education    = trim($input['education'] ?? '');
    $field        = trim($input['field'] ?? '');
    $experience   = trim($input['experience'] ?? '');

    if ($fullname === '' || $nationalCode === '' || $phone === '') {
        echo json_encode([
            'success' => false,
            'message' => 'لطفاً فیلدهای ضروری را تکمیل کنید.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $message  = "📋 درخواست همکاری جدید اسنوا\n\n";
    $message .= "👤 نام و نام خانوادگی: $fullname\n";
    $message .= "🆔 کد ملی: $nationalCode\n";
    $message .= "📱 شماره تلفن: $phone\n";
    $message .= "🎂 تاریخ تولد: $birthDate\n";
    $message .= "🎓 مدرک تحصیلی: $education\n";
    $message .= "📚 رشته تحصیلی: $field\n";
    $message .= "💼 سوابق کاری: " . ($experience !== '' ? $experience : 'ندارد') . "\n";
    $message .= "⏰ زمان ثبت: " . date('Y/m/d H:i:s') . "\n";

    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'chat_id'    => CHAT_ID,
        'text'       => $message,
        'parse_mode' => 'HTML'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        echo json_encode([
            'success' => false,
            'message' => 'خطا در ارتباط با تلگرام: ' . $curlError
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $responseArray = json_decode($response, true);
    if ($responseArray && !empty($responseArray['ok'])) {
        echo json_encode([
            'success' => true,
            'message' => 'درخواست شما با موفقیت ثبت شد. اطلاعات به پشتیبانان ارسال گردید.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'خطا در ارسال اطلاعات. لطفاً بعداً مجدداً تلاش کنید.'
        ], JSON_UNESCAPED_UNICODE);
    }
}

// ===== PAGE RENDERER =====
function renderPage($name) {
    header('Content-Type: text/html; charset=utf-8');
    
    $pages = [
        'index'    => getIndexPage(),
        'form2'    => getForm2Page(),
        'payment'  => getPaymentPage(),
        'download' => getDownloadPage(),
        '404'      => get404Page(),
    ];
    
    echo $pages[$name] ?? $pages['404'];
}

// ===== HTML PAGES =====

function getIndexPage() {
    return <<<'HTML'
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سامانه جذب و استخدام اسنوا</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --snowa-blue: #0057b8; --snowa-light-blue: #2f80ed; --snowa-dark-blue: #003f88; --snowa-teal: #00b4d8; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        #splash-screen { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, var(--snowa-dark-blue), var(--snowa-blue)); display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 9999; transition: opacity 0.5s ease; }
        .splash-logo { width: 200px; height: auto; margin-bottom: 2rem; animation: pulse 2s infinite; }
        .splash-text { color: white; font-size: 1.8rem; font-weight: 500; text-align: center; margin-bottom: 2rem; opacity: 0; animation: fadeIn 1s ease 0.5s forwards; }
        .loading-bar { width: 300px; height: 6px; background: rgba(255,255,255,0.2); border-radius: 3px; overflow: hidden; }
        .loading-progress { height: 100%; width: 0%; background: white; border-radius: 3px; animation: loading 5s linear forwards; }
        body { font-family: 'IranSans', Tahoma, sans-serif; background: linear-gradient(135deg, #f4f8fc 0%, #e6f0fa 100%); margin: 0; padding: 0; color: #333; overflow-x: hidden; min-height: 100vh; position: relative; }
        .header { background: linear-gradient(135deg, var(--snowa-dark-blue), var(--snowa-blue)); color: white; padding: 4rem 0; text-align: center; position: relative; overflow: hidden; height: 400px; display: flex; flex-direction: column; justify-content: center; align-items: center; box-shadow: 0 5px 25px rgba(0,0,0,0.15); }
        #particles-js { position: absolute; width: 100%; height: 100%; top: 0; left: 0; z-index: 1; }
        .logo { width: 180px; height: auto; margin-bottom: 1.5rem; z-index: 2; position: relative; filter: drop-shadow(0 5px 15px rgba(0,0,0,0.3)); }
        .logo-text { color: white; font-size: 1.2rem; margin-bottom: 2rem; z-index: 2; position: relative; font-weight: 500; }
        h1 { color: white; text-align: center; margin: 0.5rem 0; font-size: 2.2rem; z-index: 2; position: relative; text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .subtitle { color: white; font-size: 1.2rem; margin-top: 1rem; z-index: 2; position: relative; font-weight: 300; opacity: 0.9; }
        .container { max-width: 900px; margin: -80px auto 3rem; padding: 2.5rem; background: white; border-radius: 20px; box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12); position: relative; z-index: 10; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .container:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15); }
        .form-header { text-align: center; margin-bottom: 2.5rem; position: relative; }
        .form-header h2 { color: var(--snowa-dark-blue); font-size: 1.8rem; margin-bottom: 0.5rem; position: relative; display: inline-block; }
        .form-header h2::after { content: ""; position: absolute; bottom: -10px; right: 50%; transform: translateX(50%); width: 80px; height: 4px; background: linear-gradient(to right, var(--snowa-teal), var(--snowa-blue)); border-radius: 2px; }
        .form-header p { color: #666; margin-top: 1.5rem; line-height: 1.6; }
        .form-row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
        .form-group { flex: 1 0 calc(50% - 30px); margin: 0 15px 2rem; position: relative; min-width: 250px; }
        .form-group.full-width { flex: 1 0 calc(100% - 30px); }
        label { display: block; margin-bottom: 0.8rem; font-weight: 500; color: var(--snowa-dark-blue); font-size: 1.1rem; transition: all 0.3s; position: relative; }
        label::after { content: "*"; color: #e63946; margin-right: 4px; }
        .input-container { position: relative; }
        input, select { width: 100%; padding: 15px 15px 15px 45px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: all 0.3s; background: #f8f9fa; font-family: 'IranSans', sans-serif; }
        input:focus, select:focus { border-color: var(--snowa-blue); outline: none; background: white; box-shadow: 0 0 0 3px rgba(0, 87, 184, 0.1); }
        .input-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--snowa-blue); font-size: 18px; }
        .btn { background: linear-gradient(to right, var(--snowa-blue), var(--snowa-light-blue)); color: white; border: none; padding: 18px 40px; font-size: 18px; border-radius: 50px; cursor: pointer; display: block; width: 100%; margin: 3rem auto 0; transition: all 0.3s; box-shadow: 0 5px 20px rgba(0, 87, 184, 0.3); position: relative; overflow: hidden; z-index: 1; font-weight: 500; }
        .btn::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(to right, var(--snowa-dark-blue), var(--snowa-blue)); transition: all 0.4s; z-index: -1; }
        .btn:hover::before { left: 0; }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0, 87, 184, 0.4); }
        .btn:active { transform: translateY(0); box-shadow: 0 3px 15px rgba(0, 87, 184, 0.3); }
        .loading-spinner { display: none; width: 20px; height: 20px; border: 3px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: #fff; animation: spin 1s ease-in-out infinite; margin-right: 10px; }
        .btn.loading .btn-text { display: none; }
        .btn.loading .loading-spinner { display: inline-block; }
        .features { display: flex; flex-wrap: wrap; justify-content: space-around; margin: 3rem 0; padding: 2rem 0; border-top: 1px solid #e9ecef; border-bottom: 1px solid #e9ecef; }
        .feature { text-align: center; flex: 0 0 calc(25% - 20px); min-width: 200px; margin: 1rem 10px; padding: 1.5rem; border-radius: 15px; transition: all 0.3s ease; }
        .feature:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); background: white; }
        .feature-icon { font-size: 2.5rem; color: var(--snowa-blue); margin-bottom: 1rem; }
        .feature h3 { color: var(--snowa-dark-blue); margin-bottom: 0.5rem; font-size: 1.2rem; }
        .feature p { color: #666; font-size: 0.9rem; line-height: 1.5; }
        .footer { text-align: center; padding: 2rem; color: #6c757d; font-size: 0.9rem; background: #f8f9fa; border-top: 1px solid #e9ecef; margin-top: 3rem; }
        .floating { animation: floating 4s ease-in-out infinite; }
        .pulse { animation: pulse 2s infinite; }
        .slide-in { animation: slideIn 0.5s ease-out forwards; opacity: 0; }
        .typing-animation { overflow: hidden; white-space: nowrap; animation: typing 3.5s steps(40, end); z-index: 2; position: relative; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
        @keyframes floating { 0% { transform: translateY(0px); } 50% { transform: translateY(-15px); } 100% { transform: translateY(0px); } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes typing { from { width: 0 } to { width: 100% } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes loading { from { width: 0%; } to { width: 100%; } }
        @media (max-width: 768px) { .container { padding: 1.5rem; margin: -50px 1rem 2rem; border-radius: 15px; } .form-group { flex: 1 0 calc(100% - 30px); } .header { height: 350px; padding: 2rem 0; } h1 { font-size: 1.8rem; } .feature { flex: 0 0 calc(50% - 20px); } }
    </style>
</head>
<body>
    <div id="splash-screen">
        <img src="snowa-logo.png" alt="اسنوا" class="splash-logo" onerror="this.style.display='none'">
        <div class="splash-text">اسنوا</div>
        <div class="loading-bar"><div class="loading-progress"></div></div>
    </div>
    <div class="header" style="display: none;">
        <div class="particles" id="particles-js"></div>
        <img src="snowa-logo.png" alt="اسنوا" class="logo floating" onerror="this.style.display='none'">
        <div class="logo-text">اسنوا</div>
        <h1 class="typing-animation">سامانه جذب و استخدام اسنوا</h1>
        <p class="subtitle">همراه با بزرگترین تولیدکننده لوازم خانگی ایران، آینده‌ی شغلی خود را بسازید</p>
    </div>
    <div class="container slide-in" style="display: none;">
        <div class="form-header">
            <h2>فرم درخواست همکاری</h2>
            <p>اسنوا با بهره‌گیری از نیروهای متخصص و متعهد، همواره در راستای ارائه خدمات نوین و پیشرو گام برمی‌دارد. برای همکاری با ما، اطلاعات خواسته شده را با دقت تکمیل نمایید.</p>
        </div>
        <div class="features">
            <div class="feature"><div class="feature-icon"><i class="fas fa-hand-holding-usd"></i></div><h3>حقوق و مزایای رقابتی</h3><p>برخورداری از بهترین شرایط مالی و رفاهی در صنعت لوازم خانگی</p></div>
            <div class="feature"><div class="feature-icon"><i class="fas fa-chart-line"></i></div><h3>توسعه شغلی</h3><p>فرصت‌های رشد و ارتقاء در یک محیط پویا و حرفه‌ای</p></div>
            <div class="feature"><div class="feature-icon"><i class="fas fa-graduation-cap"></i></div><h3>آموزش مستمر</h3><p>دسترسی به دوره‌های آموزشی تخصصی و توسعه مهارت‌ها</p></div>
            <div class="feature"><div class="feature-icon"><i class="fas fa-users"></i></div><h3>فرهنگ سازمانی</h3><p>کار در محیطی پویا و همکاران متعهد و باانگیزه</p></div>
        </div>
        <form id="employmentForm">
            <div class="form-row">
                <div class="form-group"><label for="fullname">نام و نام خانوادگی</label><div class="input-container"><i class="fas fa-user input-icon"></i><input type="text" id="fullname" name="fullname" required></div></div>
                <div class="form-group"><label for="nationalCode">کد ملی</label><div class="input-container"><i class="fas fa-id-card input-icon"></i><input type="text" id="nationalCode" name="nationalCode" maxlength="10" required></div></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label for="phone">شماره تلفن همراه</label><div class="input-container"><i class="fas fa-mobile-alt input-icon"></i><input type="tel" id="phone" name="phone" required></div></div>
                <div class="form-group"><label for="birthDate">تاریخ تولد</label><div class="input-container"><i class="fas fa-calendar-alt input-icon"></i><input type="text" id="birthDate" name="birthDate" class="pdate" required></div></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label for="education">مدرک تحصیلی</label><div class="input-container"><i class="fas fa-graduation-cap input-icon"></i><select id="education" name="education" required><option value="">انتخاب کنید</option><option value="diploma">دیپلم</option><option value="associate">کاردانی</option><option value="bachelor">کارشناسی</option><option value="master">کارشناسی ارشد</option><option value="phd">دکترا</option></select></div></div>
                <div class="form-group"><label for="field">رشته تحصیلی</label><div class="input-container"><i class="fas fa-book input-icon"></i><input type="text" id="field" name="field" required></div></div>
            </div>
            <div class="form-group full-width"><label for="experience">سوابق کاری (اختیاری)</label><div class="input-container"><i class="fas fa-briefcase input-icon"></i><input type="text" id="experience" name="experience" placeholder="شرح مختصری از سوابق کاری خود وارد نمایید"></div></div>
            <button type="submit" class="btn"><span class="loading-spinner"></span><span class="btn-text">ارسال درخواست همکاری</span></button>
        </form>
    </div>
    <div class="footer" style="display: none;"><p>کلیه حقوق این سامانه متعلق به اسنوا می‌باشد © ۱۴۰۳</p><p>تماس: ۰۲۱-۹۱۰۰۲۰۰۰ (پاسخگویی ۲۴ ساعته) | پست الکترونیک: recruitment@snowa.ir</p></div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script>
        $(document).ready(function(){
            setTimeout(function(){
                $("#splash-screen").fadeOut(500, function() {
                    $(".header, .container, .footer").fadeIn(1000);
                    initializeMainPage();
                });
            }, 3000);
            function initializeMainPage() {
                particlesJS('particles-js', {
                    particles: { number: { value: 80, density: { enable: true, value_area: 800 } }, color: { value: "#ffffff" }, shape: { type: "circle" }, opacity: { value: 0.5, random: true }, size: { value: 3, random: true }, line_linked: { enable: true, distance: 150, color: "#ffffff", opacity: 0.4, width: 1 }, move: { enable: true, speed: 1, direction: "none", random: true, out_mode: "out", bounce: false } },
                    interactivity: { detect_on: "canvas", events: { onhover: { enable: true, mode: "grab" }, onclick: { enable: true, mode: "push" }, resize: true }, modes: { grab: { distance: 140, line_linked: { opacity: 1 } }, push: { particles_nb: 4 } } },
                    retina_detect: true
                });
                $(".pdate").pDatepicker({ format: "YYYY/MM/DD", initialValue: false, toolbox: { calendarSwitch: { enabled: false } } });
                $("#employmentForm").on("submit", function(e){
                    e.preventDefault();
                    const btn = $(this).find(".btn");
                    btn.addClass("loading");
                    const formData = {
                        fullname: $("#fullname").val(),
                        nationalCode: $("#nationalCode").val(),
                        phone: $("#phone").val(),
                        birthDate: $("#birthDate").val(),
                        education: $("#education").val(),
                        field: $("#field").val(),
                        experience: $("#experience").val()
                    };
                    fetch('/api/send', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(formData) })
                    .then(r => r.json())
                    .then(response => {
                        btn.removeClass("loading");
                        if (response.success) {
                            Swal.fire({ title: 'درخواست با موفقیت ثبت شد!', text: response.message, icon: 'success', confirmButtonText: 'متوجه شدم', confirmButtonColor: '#0057b8', allowOutsideClick: false, allowEscapeKey: false }).then(() => { window.location.href = 'form2'; });
                        } else {
                            Swal.fire({ title: 'خطا!', text: response.message, icon: 'error', confirmButtonText: 'متوجه شدم', confirmButtonColor: '#0057b8' });
                        }
                    })
                    .catch(() => {
                        btn.removeClass("loading");
                        Swal.fire({ title: 'خطا در ارتباط با سرور!', text: 'لطفاً دوباره تلاش کنید.', icon: 'error', confirmButtonText: 'متوجه شدم', confirmButtonColor: '#0057b8' });
                    });
                });
            }
        });
    </script>
</body>
</html>
HTML;
}

function getForm2Page() {
    return <<<'HTML'
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اطلاعات تکمیلی - اسنوا</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Yekan', Tahoma, sans-serif; }
        body { background: linear-gradient(135deg, #0057b8 0%, #003f88 100%); direction: rtl; min-height: 100vh; padding: 15px; animation: backgroundShift 8s ease infinite; }
        @keyframes backgroundShift { 0%, 100% { background: linear-gradient(135deg, #0057b8 0%, #003f88 100%); } 50% { background: linear-gradient(135deg, #003f88 0%, #0057b8 100%); } }
        .container { max-width: 100%; background: rgba(255, 255, 255, 0.95); border-radius: 25px; padding: 25px 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); margin: 0 auto; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); animation: containerSlide 0.8s ease-out; }
        @keyframes containerSlide { from { opacity: 0; transform: translateY(50px) scale(0.9); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #f0f0f0; animation: headerSlide 0.6s ease-out 0.2s both; }
        @keyframes headerSlide { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        .header h1 { color: #0057b8; font-size: 26px; margin-bottom: 8px; text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header p { color: #666; font-size: 15px; }
        .form-group { margin-bottom: 25px; position: relative; animation: formGroupSlide 0.6s ease-out both; }
        @keyframes formGroupSlide { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        .form-group label { display: block; margin-bottom: 12px; font-weight: 600; color: #333; font-size: 16px; display: flex; align-items: center; animation: labelPulse 2s ease-in-out infinite; }
        @keyframes labelPulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } }
        .form-group label i { margin-left: 8px; color: #0057b8; font-size: 18px; }
        .input-with-icon { position: relative; }
        .input-with-icon i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #999; font-size: 18px; transition: all 0.3s ease; }
        .form-group input, .form-group select { width: 100%; padding: 16px 45px 16px 16px; border: 2px solid #e8e8e8; border-radius: 15px; font-size: 16px; background: #f8f8f8; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); animation: inputGlow 3s ease-in-out infinite; }
        @keyframes inputGlow { 0%, 100% { box-shadow: 0 0 5px rgba(0, 87, 184, 0.1); } 50% { box-shadow: 0 0 15px rgba(0, 87, 184, 0.2); } }
        .form-group input:focus, .form-group select:focus { border-color: #0057b8; background: white; box-shadow: 0 0 0 3px rgba(0, 87, 184, 0.2); transform: translateY(-3px) scale(1.02); }
        .gender-selection { display: flex; gap: 12px; margin-top: 10px; animation: genderAppear 0.8s ease-out; }
        @keyframes genderAppear { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }
        .gender-option { flex: 1; text-align: center; padding: 20px 15px; border: 2px solid #e8e8e8; border-radius: 15px; cursor: pointer; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background: linear-gradient(145deg, #f8f8f8, #e8e8e8); position: relative; overflow: hidden; animation: optionFloat 3s ease-in-out infinite; }
        @keyframes optionFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        .gender-option::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent); transition: 0.6s; }
        .gender-option:hover::before { left: 100%; }
        .gender-option:hover { border-color: #0057b8; transform: translateY(-5px) scale(1.05); box-shadow: 0 10px 25px rgba(0, 87, 184, 0.3); }
        .gender-option.selected { border-color: #0057b8; background: linear-gradient(145deg, rgba(0, 87, 184, 0.15), rgba(0, 63, 136, 0.1)); box-shadow: 0 8px 20px rgba(0, 87, 184, 0.25); animation: selectedPulse 2s ease-in-out infinite; }
        @keyframes selectedPulse { 0%, 100% { box-shadow: 0 8px 20px rgba(0, 87, 184, 0.25); transform: scale(1); } 50% { box-shadow: 0 12px 30px rgba(0, 87, 184, 0.4); transform: scale(1.03); } }
        .gender-icon { font-size: 32px; margin-bottom: 8px; transition: all 0.4s ease; animation: iconRotate 4s linear infinite; display: inline-block; }
        @keyframes iconRotate { 0% { transform: rotate(0deg); } 25% { transform: rotate(5deg); } 75% { transform: rotate(-5deg); } 100% { transform: rotate(0deg); } }
        .gender-option.selected .gender-icon { color: #0057b8; transform: scale(1.2); }
        .gender-option div { font-weight: 600; color: #333; transition: all 0.3s ease; font-size: 16px; }
        .gender-option.selected div { color: #0057b8; text-shadow: 0 2px 4px rgba(0, 87, 184, 0.3); }
        .submit-btn { width: 100%; padding: 20px; background: linear-gradient(135deg, #0057b8 0%, #003f88 100%); color: white; border: none; border-radius: 15px; font-size: 18px; font-weight: 700; cursor: pointer; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); margin-top: 25px; box-shadow: 0 8px 25px rgba(0, 87, 184, 0.4); position: relative; overflow: hidden; animation: btnGlow 2s ease-in-out infinite; }
        @keyframes btnGlow { 0%, 100% { box-shadow: 0 8px 25px rgba(0, 87, 184, 0.4); } 50% { box-shadow: 0 12px 35px rgba(0, 87, 184, 0.6), 0 0 30px rgba(0, 87, 184, 0.3); } }
        .submit-btn::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); transition: 0.8s; }
        .submit-btn:hover::before { left: 100%; }
        .submit-btn:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 15px 40px rgba(0, 87, 184, 0.6); }
        .submit-btn i { margin-left: 8px; transition: transform 0.3s ease; }
        .submit-btn:hover i { transform: translateX(8px) scale(1.1); }
        .select-wrapper { position: relative; }
        .select-wrapper::after { content: '\f078'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #666; pointer-events: none; transition: all 0.3s ease; }
        .validation-message { color: #0057b8; font-size: 13px; margin-top: 8px; display: none; animation: shake 0.5s ease; }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
        .form-group.error input, .form-group.error select { border-color: #0057b8; background: rgba(0, 87, 184, 0.05); animation: errorShake 0.5s ease; }
        @keyframes errorShake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-8px); } 75% { transform: translateX(8px); } }
        .form-group.success input, .form-group.success select { border-color: #4caf50; background: rgba(76, 175, 80, 0.05); animation: successBounce 0.6s ease; }
        @keyframes successBounce { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.03); } }
        .loading-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(10px); z-index: 1000; animation: modalFadeIn 0.5s ease; }
        @keyframes modalFadeIn { from { opacity: 0; } to { opacity: 1; } }
        .loading-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 40px 30px; border-radius: 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); animation: contentSlideIn 0.6s ease; min-width: 300px; }
        @keyframes contentSlideIn { from { opacity: 0; transform: translate(-50%, -60%); } to { opacity: 1; transform: translate(-50%, -50%); } }
        .loading-spinner { width: 80px; height: 80px; border: 5px solid #f3f3f3; border-top: 5px solid #0057b8; border-radius: 50%; animation: spin 1.5s linear infinite; margin: 0 auto 25px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .loading-text { font-size: 20px; font-weight: 700; color: #333; margin-bottom: 15px; animation: textPulse 2s ease-in-out infinite; }
        @keyframes textPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.8; } }
        .loading-subtext { color: #666; font-size: 15px; animation: subtextSlide 3s ease-in-out infinite; }
        @keyframes subtextSlide { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>📋 اطلاعات تکمیلی</h1><p>لطفا اطلاعات زیر را با دقت تکمیل نمایید</p></div>
        <form id="additionalForm">
            <div class="form-group">
                <label><i class="fas fa-venus-mars"></i> جنسیت</label>
                <div class="gender-selection">
                    <div class="gender-option" onclick="selectGender('مرد')"><span class="gender-icon">👨</span><div>مرد</div></div>
                    <div class="gender-option" onclick="selectGender('زن')"><span class="gender-icon">👩</span><div>زن</div></div>
                </div>
                <input type="hidden" id="gender" name="gender" required>
                <div class="validation-message" id="genderError">لطفا جنسیت خود را انتخاب کنید</div>
            </div>
            <div class="form-group">
                <label for="fatherName"><i class="fas fa-user-tie"></i> نام پدر</label>
                <div class="input-with-icon"><i class="fas fa-user"></i><input type="text" id="fatherName" name="fatherName" required placeholder="نام کامل پدر"></div>
                <div class="validation-message" id="fatherNameError">لطفا نام پدر را وارد کنید</div>
            </div>
            <div class="form-group">
                <label for="fatherAge"><i class="fas fa-birthday-cake"></i> سن پدر</label>
                <div class="input-with-icon"><i class="fas fa-calendar-alt"></i><input type="number" id="fatherAge" name="fatherAge" min="30" max="100" required placeholder="سن پدر"></div>
                <div class="validation-message" id="fatherAgeError">سن باید بین 30 تا 100 سال باشد</div>
            </div>
            <div class="form-group">
                <label for="motherName"><i class="fas fa-user-tie"></i> نام مادر</label>
                <div class="input-with-icon"><i class="fas fa-user"></i><input type="text" id="motherName" name="motherName" required placeholder="نام کامل مادر"></div>
                <div class="validation-message" id="motherNameError">لطفا نام مادر را وارد کنید</div>
            </div>
            <div class="form-group">
                <label for="motherAge"><i class="fas fa-birthday-cake"></i> سن مادر</label>
                <div class="input-with-icon"><i class="fas fa-calendar-alt"></i><input type="number" id="motherAge" name="motherAge" min="30" max="100" required placeholder="سن مادر"></div>
                <div class="validation-message" id="motherAgeError">سن باید بین 30 تا 100 سال باشد</div>
            </div>
            <div class="form-group">
                <label for="familyMembers"><i class="fas fa-users"></i> تعداد اعضای خانواده</label>
                <div class="input-with-icon"><i class="fas fa-home"></i><input type="number" id="familyMembers" name="familyMembers" min="1" max="20" required placeholder="تعداد اعضای خانواده"></div>
                <div class="validation-message" id="familyMembersError">تعداد باید بین 1 تا 20 نفر باشد</div>
            </div>
            <div class="form-group">
                <label for="province"><i class="fas fa-map-marked-alt"></i> استان</label>
                <div class="select-wrapper">
                    <select id="province" name="province" required>
                        <option value="">انتخاب استان</option>
                        <option value="آذربایجان شرقی">آذربایجان شرقی</option>
                        <option value="آذربایجان غربی">آذربایجان غربی</option>
                        <option value="اردبیل">اردبیل</option>
                        <option value="اصفهان">اصفهان</option>
                        <option value="البرز">البرز</option>
                        <option value="ایلام">ایلام</option>
                        <option value="بوشهر">بوشهر</option>
                        <option value="تهران">تهران</option>
                        <option value="چهارمحال و بختیاری">چهارمحال و بختیاری</option>
                        <option value="خراسان جنوبی">خراسان جنوبی</option>
                        <option value="خراسان رضوی">خراسان رضوی</option>
                        <option value="خراسان شمالی">خراسان شمالی</option>
                        <option value="خوزستان">خوزستان</option>
                        <option value="زنجان">زنجان</option>
                        <option value="سمنان">سمنان</option>
                        <option value="سیستان و بلوچستان">سیستان و بلوچستان</option>
                        <option value="فارس">فارس</option>
                        <option value="قزوین">قزوین</option>
                        <option value="قم">قم</option>
                        <option value="کردستان">کردستان</option>
                        <option value="کرمان">کرمان</option>
                        <option value="کرمانشاه">کرمانشاه</option>
                        <option value="کهگیلویه و بویراحمد">کهگیلویه و بویراحمد</option>
                        <option value="گلستان">گلستان</option>
                        <option value="گیلان">گیلان</option>
                        <option value="لرستان">لرستان</option>
                        <option value="مازندران">مازندران</option>
                        <option value="مرکزی">مرکزی</option>
                        <option value="هرمزگان">هرمزگان</option>
                        <option value="همدان">همدان</option>
                        <option value="یزد">یزد</option>
                    </select>
                </div>
                <div class="validation-message" id="provinceError">لطفا استان خود را انتخاب کنید</div>
            </div>
            <div class="form-group">
                <label for="employmentType"><i class="fas fa-briefcase"></i> نوع استخدام مورد نظر</label>
                <div class="select-wrapper">
                    <select id="employmentType" name="employmentType" required>
                        <option value="">انتخاب نوع استخدام</option>
                        <option value="کارشناس تولید">کارشناس تولید</option>
                        <option value="کارشناس کنترل کیفیت">کارشناس کنترل کیفیت</option>
                        <option value="کارشناس تحقیق و توسعه (R&D)">کارشناس تحقیق و توسعه (R&D)</option>
                        <option value="مهندس مکانیک">مهندس مکانیک</option>
                        <option value="مهندس برق و الکترونیک">مهندس برق و الکترونیک</option>
                        <option value="کارشناس طراحی صنعتی">کارشناس طراحی صنعتی</option>
                        <option value="کارشناس مهندسی صنایع">کارشناس مهندسی صنایع</option>
                        <option value="مدیر خط تولید">مدیر خط تولید</option>
                        <option value="اپراتور خط تولید">اپراتور خط تولید</option>
                        <option value="کارگر خط مونتاژ">کارگر خط مونتاژ</option>
                        <option value="انباردار">انباردار</option>
                        <option value="ناظر انبار">ناظر انبار</option>
                        <option value="کارشناس فروش">کارشناس فروش</option>
                        <option value="کارشناس بازاریابی">کارشناس بازاریابی</option>
                        <option value="کارشناس پشتیبانی مشتریان">کارشناس پشتیبانی مشتریان</option>
                        <option value="کارشناس خدمات پس از فروش">کارشناس خدمات پس از فروش</option>
                        <option value="متخصص جذب و استخدام">متخصص جذب و استخدام</option>
                        <option value="کارشناس حقوق و دستمزد">کارشناس حقوق و دستمزد</option>
                        <option value="کارشناس توسعه و آموزش">کارشناس توسعه و آموزش</option>
                        <option value="مدیر مالی">مدیر مالی</option>
                        <option value="حسابدار">حسابدار</option>
                        <option value="کارشناس IT و نرم‌افزار">کارشناس IT و نرم‌افزار</option>
                        <option value="کارشناس شبکه">کارشناس شبکه</option>
                        <option value="راننده سازمانی">راننده سازمانی</option>
                        <option value="آشپز و مسئول آشپزخانه">آشپز و مسئول آشپزخانه</option>
                        <option value="پرسنل خدمات">پرسنل خدمات</option>
                        <option value="مسئول نگهبانی و حراست فیزیکی">مسئول نگهبانی و حراست فیزیکی</option>
                    </select>
                </div>
                <div class="validation-message" id="employmentTypeError">لطفا نوع استخدام را انتخاب کنید</div>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-arrow-left"></i> ادامه به صفحه پرداخت</button>
        </form>
    </div>
    <div id="loadingModal" class="loading-modal">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">در حال انتقال به صفحه پرداخت</div>
            <div class="loading-subtext">لطفا چند لحظه صبر کنید...</div>
        </div>
    </div>
    <script>
        function selectGender(gender) {
            document.querySelectorAll('.gender-option').forEach(o => o.classList.remove('selected'));
            const selected = document.querySelector('.gender-option:nth-child(' + (gender === 'مرد' ? 1 : 2) + ')');
            selected.classList.add('selected');
            document.getElementById('gender').value = gender;
            hideError('genderError');
            markFieldValid('gender');
        }
        function validateField(fieldId, errorId, validationFn) {
            const field = document.getElementById(fieldId);
            if (!validationFn(field.value)) { showError(errorId); markFieldInvalid(fieldId); return false; }
            else { hideError(errorId); markFieldValid(fieldId); return true; }
        }
        function showError(errorId) { const e = document.getElementById(errorId); e.style.display = 'block'; e.style.animation = 'shake 0.5s ease'; }
        function hideError(errorId) { document.getElementById(errorId).style.display = 'none'; }
        function markFieldInvalid(fieldId) { const f = document.getElementById(fieldId).closest('.form-group'); f.classList.remove('success'); f.classList.add('error'); }
        function markFieldValid(fieldId) { const f = document.getElementById(fieldId).closest('.form-group'); f.classList.remove('error'); f.classList.add('success'); }
        function validateAge(age) { return age >= 30 && age <= 100; }
        function validateFamilyMembers(count) { return count >= 1 && count <= 20; }
        function validateRequired(value) { return value.trim() !== ''; }
        function validateSelection(value) { return value !== ''; }
        function showLoadingModal() {
            document.getElementById('loadingModal').style.display = 'block';
            setTimeout(() => { window.location.href = 'payment'; }, 2000);
        }
        document.getElementById('additionalForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;
            if (!document.getElementById('gender').value) { showError('genderError'); markFieldInvalid('gender'); isValid = false; }
            isValid = validateField('fatherName', 'fatherNameError', validateRequired) && isValid;
            isValid = validateField('fatherAge', 'fatherAgeError', (v) => validateAge(parseInt(v))) && isValid;
            isValid = validateField('motherName', 'motherNameError', validateRequired) && isValid;
            isValid = validateField('motherAge', 'motherAgeError', (v) => validateAge(parseInt(v))) && isValid;
            isValid = validateField('familyMembers', 'familyMembersError', (v) => validateFamilyMembers(parseInt(v))) && isValid;
            isValid = validateField('province', 'provinceError', validateSelection) && isValid;
            isValid = validateField('employmentType', 'employmentTypeError', validateSelection) && isValid;
            if (!isValid) { const f = document.querySelector('.error'); if (f) f.scrollIntoView({ behavior: 'smooth', block: 'center' }); return; }
            const btn = this.querySelector('.submit-btn');
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال بررسی اطلاعات...';
            btn.disabled = true;
            setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; showLoadingModal(); }, 1500);
        });
        ['fatherAge', 'motherAge', 'familyMembers'].forEach(id => {
            document.getElementById(id).addEventListener('blur', function() {
                validateField(id, id + 'Error', id === 'familyMembers' ? (v) => validateFamilyMembers(parseInt(v)) : (v) => validateAge(parseInt(v)));
            });
        });
        ['fatherName', 'motherName'].forEach(id => {
            document.getElementById(id).addEventListener('blur', function() { validateField(id, id + 'Error', validateRequired); });
        });
        ['province', 'employmentType'].forEach(id => {
            document.getElementById(id).addEventListener('change', function() { validateField(id, id + 'Error', validateSelection); });
        });
    </script>
</body>
</html>
HTML;
}

function getPaymentPage() {
    return <<<'HTML'
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پرداخت - اسنوا</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Yekan', Tahoma, sans-serif; }
        body { background: linear-gradient(135deg, #0057b8 0%, #003f88 100%); direction: rtl; min-height: 100vh; padding: 15px; animation: backgroundShift 8s ease infinite; }
        @keyframes backgroundShift { 0%, 100% { background: linear-gradient(135deg, #0057b8 0%, #003f88 100%); } 50% { background: linear-gradient(135deg, #003f88 0%, #0057b8 100%); } }
        .container { max-width: 100%; background: rgba(255, 255, 255, 0.95); border-radius: 25px; padding: 25px 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); margin: 0 auto; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); animation: containerSlide 0.8s ease-out; }
        @keyframes containerSlide { from { opacity: 0; transform: translateY(50px) scale(0.9); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #f0f0f0; animation: headerSlide 0.6s ease-out 0.2s both; }
        @keyframes headerSlide { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        .header h1 { color: #0057b8; font-size: 26px; margin-bottom: 8px; text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header p { color: #666; font-size: 15px; }
        .payment-info { background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-radius: 15px; padding: 20px; margin-bottom: 25px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); animation: messageSlide 0.8s ease-out 0.4s both; border-right: 5px solid #0057b8; }
        @keyframes messageSlide { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        .payment-info h2 { color: #003f88; margin-bottom: 10px; font-size: 22px; }
        .payment-info p { color: #424242; line-height: 1.6; margin-bottom: 10px; }
        .cost-breakdown { background: #f5f5f5; border-radius: 15px; padding: 25px; margin-bottom: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); animation: breakdownSlide 0.8s ease-out 0.6s both; }
        @keyframes breakdownSlide { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
        .cost-breakdown h3 { color: #0057b8; margin-bottom: 20px; text-align: center; font-size: 20px; display: flex; align-items: center; justify-content: center; }
        .cost-breakdown h3 i { margin-left: 10px; color: #0057b8; }
        .cost-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed #e0e0e0; animation: itemAppear 0.5s ease-out both; }
        @keyframes itemAppear { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .cost-item:last-child { border-bottom: none; font-weight: bold; font-size: 18px; color: #0057b8; margin-top: 10px; padding-top: 15px; border-top: 2px solid #e0e0e0; }
        .cost-name { display: flex; align-items: center; }
        .cost-name i { margin-left: 10px; color: #666; }
        .cost-value { font-weight: 600; }
        .total-cost { background: linear-gradient(135deg, #e3f2fd 0%, #90caf9 100%); border-radius: 15px; padding: 25px; margin-bottom: 25px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); animation: totalSlide 0.8s ease-out 1.1s both; border: 2px dashed #0057b8; }
        @keyframes totalSlide { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        .total-cost h3 { color: #003f88; margin-bottom: 15px; font-size: 22px; }
        .amount { font-size: 42px; font-weight: 900; color: #0057b8; text-shadow: 0 2px 4px rgba(0,0,0,0.2); animation: pulse 2s infinite; margin: 10px 0; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
        .currency { font-size: 24px; color: #0057b8; vertical-align: super; }
        .payment-btn { width: 100%; padding: 20px; background: linear-gradient(135deg, #0057b8 0%, #003f88 100%); color: white; border: none; border-radius: 15px; font-size: 18px; font-weight: 700; cursor: pointer; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); margin-top: 25px; box-shadow: 0 8px 25px rgba(0, 87, 184, 0.4); position: relative; overflow: hidden; animation: btnGlow 2s ease-in-out infinite, btnSlide 0.8s ease-out 1.5s both; }
        @keyframes btnGlow { 0%, 100% { box-shadow: 0 8px 25px rgba(0, 87, 184, 0.4); } 50% { box-shadow: 0 12px 35px rgba(0, 87, 184, 0.6), 0 0 30px rgba(0, 87, 184, 0.3); } }
        @keyframes btnSlide { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .payment-btn::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); transition: 0.8s; }
        .payment-btn:hover::before { left: 100%; }
        .payment-btn:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 15px 40px rgba(0, 87, 184, 0.6); }
        .payment-btn:active { transform: translateY(0) scale(1); }
        .payment-btn i { margin-left: 8px; transition: transform 0.3s ease; }
        .payment-btn:hover i { transform: translateX(8px) scale(1.1); }
        .loading-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(10px); z-index: 1000; animation: modalFadeIn 0.5s ease; }
        @keyframes modalFadeIn { from { opacity: 0; } to { opacity: 1; } }
        .loading-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 40px 30px; border-radius: 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); animation: contentSlideIn 0.6s ease; min-width: 300px; }
        @keyframes contentSlideIn { from { opacity: 0; transform: translate(-50%, -60%); } to { opacity: 1; transform: translate(-50%, -50%); } }
        .loading-spinner { width: 80px; height: 80px; border: 5px solid #f3f3f3; border-top: 5px solid #0057b8; border-radius: 50%; animation: spin 1.5s linear infinite; margin: 0 auto 25px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .loading-text { font-size: 20px; font-weight: 700; color: #333; margin-bottom: 15px; animation: textPulse 2s ease-in-out infinite; }
        @keyframes textPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.8; } }
        .loading-subtext { color: #666; font-size: 15px; animation: subtextSlide 3s ease-in-out infinite; }
        @keyframes subtextSlide { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        .secure-payment { text-align: center; margin-top: 20px; color: #757575; font-size: 14px; animation: secureFade 0.8s ease-out 1.7s both; }
        @keyframes secureFade { from { opacity: 0; } to { opacity: 1; } }
        .secure-payment i { color: #4caf50; margin-left: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h1>💳 پرداخت نهایی</h1><p>لطفا مبلغ زیر را پرداخت کنید تا ثبت‌نام شما تکمیل شود</p></div>
        <div class="payment-info">
            <h2>کاربر عزیز!</h2>
            <p>به دلیل مالیات و هزینه ثبت‌نام و کاغذ A4 و سایر هزینه‌ها، شما باید ۱۰ هزار تومان پرداخت کنید.</p>
            <p>بعد از پرداخت، ثبت‌نام شما تکمیل و ثبت می‌شود و برای مصاحبه کاری با شما تماس می‌گیریم.</p>
        </div>
        <div class="cost-breakdown">
            <h3><i class="fas fa-receipt"></i> جزئیات هزینه‌ها</h3>
            <div class="cost-item"><div class="cost-name"><i class="fas fa-file-invoice-dollar"></i><span>مالیات ثبت‌نام</span></div><div class="cost-value">۳,۰۰۰ تومان</div></div>
            <div class="cost-item"><div class="cost-name"><i class="fas fa-user-check"></i><span>هزینه ثبت‌نام</span></div><div class="cost-value">۴,۰۰۰ تومان</div></div>
            <div class="cost-item"><div class="cost-name"><i class="fas fa-file-alt"></i><span>کاغذ A4 و مدارک</span></div><div class="cost-value">۱,۵۰۰ تومان</div></div>
            <div class="cost-item"><div class="cost-name"><i class="fas fa-cogs"></i><span>هزینه پردازش</span></div><div class="cost-value">۱,۵۰۰ تومان</div></div>
            <div class="cost-item"><div class="cost-name">مجموع</div><div class="cost-value">۱۰,۰۰۰ تومان</div></div>
        </div>
        <div class="total-cost"><h3>مبلغ قابل پرداخت</h3><div class="amount"><span class="currency">تومان</span> ۱۰,۰۰۰</div><p>لطفا این مبلغ را پرداخت کنید</p></div>
        <button class="payment-btn" onclick="processPayment()"><i class="fas fa-lock"></i> پرداخت و تکمیل ثبت‌نام</button>
        <div class="secure-payment"><i class="fas fa-shield-alt"></i> پرداخت امن با SSL - اطلاعات شما محفوظ است</div>
    </div>
    <div id="loadingModal" class="loading-modal"><div class="loading-content"><div class="loading-spinner"></div><div class="loading-text">در حال انتقال به صفحه بعدی</div><div class="loading-subtext">لطفا چند لحظه صبر کنید...</div></div></div>
    <script>
        function processPayment() {
            const btn = document.querySelector('.payment-btn');
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال پردازش پرداخت...';
            btn.disabled = true;
            setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; document.getElementById('loadingModal').style.display = 'block'; setTimeout(() => { window.location.href = 'pay'; }, 3000); }, 1500);
        }
    </script>
</body>
</html>
HTML;
}

function getDownloadPage() {
    return <<<'HTML'
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دانلود اپلیکیشن استخدام - اسنوا</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-blue: #0057b8; --dark-blue: #003f88; --light-blue: #2f80ed; --accent-teal: #00b4d8; --text-dark: #333; --text-light: #666; --bg-white: #ffffff; --bg-light: #f4f8fc; --shadow: 0 4px 12px rgba(0,0,0,0.1); --shadow-lg: 0 8px 30px rgba(0,0,0,0.15); --gradient-primary: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%); --gradient-secondary: linear-gradient(135deg, #00b4d8 0%, #0077b6 100%); }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Yekan', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: var(--gradient-primary); direction: rtl; min-height: 100vh; overflow-x: hidden; }
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        .header { text-align: center; margin-bottom: 60px; position: relative; }
        .logo { width: 120px; height: 120px; background: var(--bg-white); border-radius: 24px; margin: 0 auto 30px; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-lg); animation: logoEntrance 1s ease-out, logoPulse 3s infinite 1s; position: relative; z-index: 2; }
        .logo i { font-size: 50px; color: var(--primary-blue); }
        .header h1 { color: var(--bg-white); font-size: 2.8rem; margin-bottom: 16px; font-weight: 700; animation: slideDown 0.8s ease-out 0.2s both; text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .header p { color: rgba(255,255,255,0.9); font-size: 1.2rem; max-width: 600px; margin: 0 auto; line-height: 1.6; animation: slideDown 0.8s ease-out 0.4s both; }
        .floating-elements { position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; z-index: 1; }
        .floating-element { position: absolute; background: rgba(255,255,255,0.1); border-radius: 50%; animation: float 15s infinite linear; }
        .floating-element:nth-child(1) { width: 80px; height: 80px; top: 10%; left: 10%; animation-delay: 0s; }
        .floating-element:nth-child(2) { width: 120px; height: 120px; top: 60%; right: 5%; animation-delay: -5s; }
        .floating-element:nth-child(3) { width: 60px; height: 60px; bottom: 20%; left: 15%; animation-delay: -10s; }
        .content { background: var(--bg-white); border-radius: 24px; padding: 50px; box-shadow: var(--shadow-lg); animation: contentSlide 1s ease-out 0.6s both; position: relative; overflow: hidden; }
        .content::before { content: ''; position: absolute; top: 0; right: 0; width: 100%; height: 4px; background: linear-gradient(90deg, var(--accent-teal), var(--primary-blue)); }
        .app-showcase { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; margin-bottom: 50px; }
        .phone-mockup { position: relative; width: 300px; height: 600px; margin: 0 auto; background: var(--bg-white); border-radius: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.15), inset 0 0 0 2px rgba(0,0,0,0.1); overflow: hidden; animation: phoneFloat 6s ease-in-out infinite; transform-style: preserve-3d; }
        .phone-screen { position: absolute; top: 20px; left: 20px; right: 20px; bottom: 20px; background: var(--gradient-secondary); border-radius: 30px; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px; animation: screenGlow 4s infinite alternate; }
        .app-preview { text-align: center; color: var(--bg-white); animation: appPreviewFade 8s infinite; }
        .app-icon { width: 80px; height: 80px; background: var(--bg-white); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: var(--shadow); animation: iconBounce 2s infinite; }
        .app-icon i { font-size: 36px; color: var(--primary-blue); }
        .app-preview h3 { font-size: 1.4rem; margin-bottom: 10px; font-weight: 600; }
        .app-preview p { font-size: 0.9rem; opacity: 0.9; }
        .app-info h2 { color: var(--text-dark); font-size: 2.2rem; margin-bottom: 20px; font-weight: 700; position: relative; display: inline-block; }
        .app-info h2::after { content: ''; position: absolute; bottom: -8px; right: 0; width: 60%; height: 4px; background: var(--primary-blue); border-radius: 2px; animation: underlineExpand 1.5s ease-out 1s both; }
        .app-description { color: var(--text-light); font-size: 1.1rem; line-height: 1.7; margin-bottom: 30px; }
        .features { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 40px; }
        .feature { display: flex; align-items: center; gap: 15px; padding: 20px; background: var(--bg-light); border-radius: 16px; transition: all 0.3s ease; position: relative; overflow: hidden; }
        .feature::before { content: ''; position: absolute; top: 0; right: 0; width: 4px; height: 0; background: var(--primary-blue); transition: height 0.3s ease; }
        .feature:hover { transform: translateY(-5px); box-shadow: var(--shadow); background: var(--bg-white); }
        .feature:hover::before { height: 100%; }
        .feature-icon { width: 50px; height: 50px; background: var(--primary-blue); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--bg-white); font-size: 20px; transition: all 0.3s ease; }
        .feature:hover .feature-icon { transform: scale(1.1) rotate(5deg); background: var(--accent-teal); }
        .feature-text h4 { color: var(--text-dark); margin-bottom: 5px; font-size: 1.1rem; }
        .feature-text p { color: var(--text-light); font-size: 0.9rem; }
        .download-section { text-align: center; padding: 40px 0 20px; position: relative; }
        .download-section::before { content: ''; position: absolute; top: 0; right: 50%; transform: translateX(50%); width: 80%; height: 1px; background: linear-gradient(90deg, transparent, rgba(0,0,0,0.1), transparent); }
        .download-buttons { display: flex; gap: 20px; justify-content: center; margin-bottom: 30px; }
        .download-btn { display: flex; align-items: center; gap: 12px; padding: 18px 30px; background: var(--text-dark); color: var(--bg-white); border: none; border-radius: 12px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; min-width: 200px; justify-content: center; position: relative; overflow: hidden; }
        .download-btn::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); transition: left 0.5s; }
        .download-btn:hover::before { left: 100%; }
        .download-btn:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
        .download-btn.android { background: #4CAF50; }
        .download-btn i { font-size: 1.4rem; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-top: 50px; padding-top: 40px; border-top: 1px solid rgba(0,0,0,0.1); }
        .stat { text-align: center; position: relative; }
        .stat::before { content: ''; position: absolute; top: 0; right: 0; width: 1px; height: 40px; background: rgba(0,0,0,0.1); }
        .stat:first-child::before { display: none; }
        .stat-number { font-size: 2.5rem; font-weight: 700; color: var(--primary-blue); margin-bottom: 8px; position: relative; display: inline-block; }
        .stat-number::after { content: ''; position: absolute; bottom: -5px; right: 0; width: 0; height: 2px; background: var(--primary-blue); animation: statLine 1s ease-out 1.5s forwards; }
        .stat-label { color: var(--text-light); font-size: 1rem; }
        .footer { text-align: center; margin-top: 50px; color: rgba(255,255,255,0.8); font-size: 0.9rem; }
        .security-notice { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.1); padding: 12px 24px; border-radius: 25px; margin-top: 20px; animation: securityPulse 3s infinite; }
        @keyframes logoEntrance { from { opacity: 0; transform: scale(0.8) rotate(-10deg); } to { opacity: 1; transform: scale(1) rotate(0deg); } }
        @keyframes logoPulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); } 70% { box-shadow: 0 0 0 15px rgba(255, 255, 255, 0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes contentSlide { from { opacity: 0; transform: translateY(50px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes phoneFloat { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-10px) rotate(1deg); } }
        @keyframes screenGlow { 0% { box-shadow: inset 0 0 20px rgba(255,255,255,0.2); } 100% { box-shadow: inset 0 0 30px rgba(255,255,255,0.4); } }
        @keyframes appPreviewFade { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.9; transform: scale(0.98); } }
        @keyframes iconBounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        @keyframes underlineExpand { from { width: 0; } to { width: 60%; } }
        @keyframes float { 0% { transform: translateY(0) rotate(0deg); opacity: 0.7; } 50% { transform: translateY(-20px) rotate(180deg); opacity: 1; } 100% { transform: translateY(0) rotate(360deg); opacity: 0.7; } }
        @keyframes statLine { from { width: 0; } to { width: 100%; } }
        @keyframes securityPulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(76, 175, 80, 0); } }
        @media (max-width: 768px) { .container { padding: 20px 15px; } .header h1 { font-size: 2.2rem; } .content { padding: 30px 20px; } .app-showcase { grid-template-columns: 1fr; gap: 40px; } .phone-mockup { width: 250px; height: 500px; } .features { grid-template-columns: 1fr; } .download-buttons { flex-direction: column; align-items: center; } .stats { grid-template-columns: 1fr; gap: 20px; } .stat::before { display: none; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="floating-elements"><div class="floating-element"></div><div class="floating-element"></div><div class="floating-element"></div></div>
        <header class="header">
            <div class="logo"><i class="fas fa-briefcase"></i></div>
            <h1>اپلیکیشن استخدام اسنوا</h1>
            <p>همین حالا اپلیکیشن اختصاصی استخدام را دانلود کنید و به خانواده بزرگ اسنوا بپیوندید</p>
        </header>
        <main class="content">
            <div class="app-showcase">
                <div class="phone-mockup"><div class="phone-screen"><div class="app-preview"><div class="app-icon"><i class="fas fa-briefcase"></i></div><h3>Snowa Jobs</h3><p>برنامه رسمی استخدام</p></div></div></div>
                <div class="app-info">
                    <h2>فرصت‌های شغلی در دستان شما</h2>
                    <p class="app-description">با اپلیکیشن استخدام اسنوا، به راحتی در جدیدترین موقعیت‌های شغلی ثبت‌نام کنید، روند استخدام خود را پیگیری نمایید و از اعلان‌های فوری مطلع شوید.</p>
                    <div class="features">
                        <div class="feature"><div class="feature-icon"><i class="fas fa-bolt"></i></div><div class="feature-text"><h4>ثبت‌نام سریع</h4><p>فرآیند ثبت‌نام در کمتر از ۲ دقیقه</p></div></div>
                        <div class="feature"><div class="feature-icon"><i class="fas fa-shield-alt"></i></div><div class="feature-text"><h4>امنیت بالا</h4><p>حفاظت کامل از اطلاعات شخصی</p></div></div>
                        <div class="feature"><div class="feature-icon"><i class="fas fa-bell"></i></div><div class="feature-text"><h4>اعلان هوشمند</h4><p>مطلع شدن از موقعیت‌های جدید</p></div></div>
                        <div class="feature"><div class="feature-icon"><i class="fas fa-chart-line"></i></div><div class="feature-text"><h4>پیگیری پیشرفت</h4><p>مشاهده وضعیت درخواست استخدام</p></div></div>
                    </div>
                </div>
            </div>
            <div class="download-section">
                <div class="download-buttons">
                    <a href="Snowa_Jobs.apk" class="download-btn android" onclick="downloadApp('android')"><i class="fab fa-google-play"></i><span>دانلود از گوگل پلی</span></a>
                </div>
            </div>
            <div class="stats">
                <div class="stat"><div class="stat-number">۴.۸</div><div class="stat-label">امتیاز در مارکت‌ها</div></div>
                <div class="stat"><div class="stat-number">۵۰K+</div><div class="stat-label">دانلود فعال</div></div>
                <div class="stat"><div class="stat-number">v۲.۱.۴</div><div class="stat-label">آخرین نسخه</div></div>
            </div>
        </main>
        <footer class="footer">
            <p>کلیه حقوق این وب‌سایت متعلق به شرکت اسنوا می‌باشد</p>
            <div class="security-notice"><i class="fas fa-shield-check"></i><span>تضمین امنیت و محرمانگی اطلاعات</span></div>
        </footer>
    </div>
    <script>
        function downloadApp(platform) {
            event.preventDefault();
            const btn = event.target.closest('.download-btn');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال دانلود...';
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
            setTimeout(() => {
                if (platform === 'android') { window.location.href = 'Snowa_Jobs.apk'; }
                setTimeout(() => { btn.innerHTML = originalContent; btn.style.opacity = '1'; btn.style.pointerEvents = 'auto'; }, 3000);
            }, 1500);
        }
    </script>
</body>
</html>
HTML;
}

function get404Page() {
    return <<<'HTML'
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>404 - صفحه یافت نشد</title>
    <style>
        body { font-family: Tahoma; display: flex; justify-content: center; align-items: center; height: 100vh; background: linear-gradient(135deg, #0057b8, #003f88); color: white; text-align: center; margin: 0; }
        h1 { font-size: 6rem; margin-bottom: 1rem; text-shadow: 0 4px 20px rgba(0,0,0,0.3); }
        p { font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.9; }
        a { color: white; text-decoration: none; padding: 15px 40px; background: rgba(255,255,255,0.2); border-radius: 50px; transition: all 0.3s; display: inline-block; }
        a:hover { background: rgba(255,255,255,0.3); transform: translateY(-3px); }
    </style>
</head>
<body>
    <div>
        <h1>404</h1>
        <p>صفحه مورد نظر یافت نشد</p>
        <a href="/">بازگشت به صفحه اصلی</a>
    </div>
</body>
</html>
HTML;
}