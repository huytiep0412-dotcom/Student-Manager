<?php
/**
 * Plugin Name: AA EPU Notification and Stats
 * Description: Công cụ hỗ trợ học tập cho sinh viên EPU - Nguyen Huy Tiep
 * Version: 1.0.0
 * Author: Nguyen Huy Tiep
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * YÊU CẦU 1: Thanh thông báo bản "Tự động đẩy Header"
 */
add_action( 'wp_footer', 'tiep_notification_push_header' );
function tiep_notification_push_header() {
    if ( ! is_singular( 'lp_course' ) ) return;

    $message = "";
    if ( is_user_logged_in() ) {
        $user_name = wp_get_current_user()->display_name;
        $message = "Chào <strong>{$user_name}</strong>, bạn đã sẵn sàng bắt đầu bài học hôm nay chưa?";
    } else {
        $message = '<a href="' . wp_login_url( get_permalink() ) . '" style="color:#f39c12">Đăng nhập</a> để lưu tiến độ học tập!';
    }

    ?>
    <div id="epu-notify-push" style="background:#2c3e50; color:#fff; text-align:center; padding:15px; border-bottom:3px solid #f39c12; font-family:sans-serif; display:none;">
        <?php echo $message; ?>
    </div>
    
    <script>
        // Lệnh này sẽ di chuyển thanh thông báo lên trên cùng của thẻ body
        var notifyBar = document.getElementById('epu-notify-push');
        document.body.prepend(notifyBar);
        notifyBar.style.display = 'block';
    </script>
    <?php
}
// 2. YÊU CẦU 2: Shortcode thống kê (Bản an toàn)
add_shortcode( 'lp_course_info', 'tiep_lp_course_info_func' );
function tiep_lp_course_info_func( $atts ) {
    // Nếu chưa có LearnPress thì không chạy để tránh lỗi Fatal
    if ( ! class_exists( 'LearnPress' ) ) return "Vui lòng cài đặt plugin LearnPress!";

    $atts = shortcode_atts( array( 'id' => get_the_ID() ), $atts );
    $course = learn_press_get_course( $atts['id'] );
    if ( ! $course ) return "Không tìm thấy khóa học!";

    $lessons = count( $course->get_items( 'lp_lesson' ) );
    $duration = $course->get_duration();
    
    $user = learn_press_get_current_user();
    $status = "Chưa đăng ký";
    if ( is_user_logged_in() ) {
        if ( $user->has_finished_course( $atts['id'] ) ) $status = "Đã hoàn thành";
        elseif ( $user->has_enrolled_course( $atts['id'] ) ) $status = "Đã đăng ký";
    }

    return "
    <div style='border:2px solid #2c3e50; padding:15px; border-radius:10px; background:#fff;'>
        <p>📚 <b>Số bài học:</b> $lessons</p>
        <p>⏳ <b>Thời gian:</b> $duration</p>
        <p>✅ <b>Trạng thái:</b> $status</p>
    </div>";
}

/**
 * YÊU CẦU 3: Tùy biến Style (Custom CSS)
 * Đổi màu nút Enroll sang CAM và Finish sang XANH LÁ
 */
add_action( 'wp_head', 'epu_custom_brand_colors' );
function epu_custom_brand_colors() {
    ?>
    <style type="text/css">
        /* 1. NÚT ENROLL (GHI DANH) - MÀU CAM */
        .lp-course-buttons .button-enroll-course, 
        .lp-course-buttons .lp-button.button-enroll-course,
        .course-standard-buttons .button-enroll-course,
        #learn-press-course-buttons .button-enroll-course {
            background-color: #ff6600 !important; /* Màu cam thương hiệu */
            background: #ff6600 !important;
            border-color: #e65c00 !important;
            color: #ffffff !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            transition: all 0.3s ease;
        }
        
        .lp-course-buttons .button-enroll-course:hover {
            background-color: #e65c00 !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2) !important;
        }

        /* 2. NÚT FINISH (HOÀN THÀNH) - MÀU XANH LÁ */
        .lp-course-buttons .button-finish-course,
        .lp-course-buttons .lp-button.button-finish-course,
        .form-button.lp-button.button-finish-course,
        button.button-finish-course {
            background-color: #2ecc71 !important; /* Màu xanh lá thương hiệu */
            background: #2ecc71 !important;
            border-color: #27ae60 !important;
            color: #ffffff !important;
            font-weight: bold !important;
        }

        .lp-course-buttons .button-finish-course:hover {
            background-color: #27ae60 !important;
        }
    </style>
    <?php
}