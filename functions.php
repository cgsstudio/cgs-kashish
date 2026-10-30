<?php
/**
 * Financial Life Skills Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Financial Life Skills
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define('CHILD_THEME_FINANCIAL_LIFE_SKILLS_VERSION', '1.0.0');

/**
 * Enqueue styles
 */
function child_enqueue_styles()
{
    wp_enqueue_style('fls-google-fonts', 'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap', array(), null);
    wp_enqueue_style('financial-life-skills-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_FINANCIAL_LIFE_SKILLS_VERSION, 'all');

}

add_action('wp_enqueue_scripts', 'child_enqueue_styles', 15);


function world_map_leaflet_shortcode()
{
    ob_start();

    wp_enqueue_style(
        'leaflet-css',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
        [],
        '1.9.4'
    );

    wp_enqueue_script(
        'leaflet-js',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        [],
        '1.9.4',
        true
    );

    /**
     * REVERTED: Global Guard removed as per user request.
     */
    // add_action( 'template_redirect', 'fls_restrict_lesson_access' );

    ?>

        <style>
            #world-map-section {
                width: 100%;
                height: 700px;
                position: relative;
                overflow: hidden;
            }

            .leaflet-container {
                background: #e3e3e3;
            }

            .leaflet-top,
            .leaflet-bottom {
                z-index: 0 !important;
            }

            .flag-marker {
                background: #1f2a66;
                border-radius: 50%;
                width: 42px;
                height: 42px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 3px solid #ffffff;
                box-shadow: 0 4px 10px rgba(0, 0, 0, .25);
                overflow: hidden;
            }

            .flag-marker img {
                width: 22px;
                height: 22px;

            }
        </style>

        <div id="world-map-section"></div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                if (typeof L === 'undefined') return;

                const map = L.map('world-map-section', {
                    zoomControl: true,
                    scrollWheelZoom: true,
                    minZoom: 2,
                    maxZoom: 5,
                    attributionControl: false
                });

                L.tileLayer(
                    'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII='
                ).addTo(map);

                const bounds = L.latLngBounds(
                    [-60, -180],
                    [85, 180]
                );

                map.fitBounds(bounds, { paddingTopLeft: [0, 80] });

                setTimeout(() => {
                    map.invalidateSize();
                    map.fitBounds(bounds, { paddingTopLeft: [0, 80] });
                }, 300);

                const activeCountries = [
                    'United States of America',
                    'Canada',
                    'United Kingdom',
                    'Netherlands',
                    'Germany',
                    'Australia'
                ];

                fetch('https://raw.githubusercontent.com/johan/world.geo.json/master/countries.geo.json')
                    .then(res => res.json())
                    .then(data => {
                        L.geoJSON(data, {
                            style: feature => ({
                                fillColor: activeCountries.includes(feature.properties.name)
                                    ? '#8bbf65'
                                    : '#6e6e6e',
                                weight: 0.6,
                                color: '#ffffff',
                                fillOpacity: 1
                            })
                        }).addTo(map);
                    });

                const locations = [
                    { lat: 37.1, lng: -95.7, flag: 'https://flagcdn.com/us.svg' },
                    { lat: 56.1, lng: -106.3, flag: 'https://flagcdn.com/ca.svg' },
                    { lat: 55.3, lng: -3.4, flag: 'https://flagcdn.com/gb.svg' },
                    { lat: 52.1, lng: 5.3, flag: 'https://flagcdn.com/nl.svg' },
                    { lat: 51.1, lng: 10.4, flag: 'https://flagcdn.com/de.svg' },
                    { lat: -25.2, lng: 133.8, flag: 'https://flagcdn.com/au.svg' }
                ];

                locations.forEach(loc => {
                    L.marker([loc.lat, loc.lng], {
                        icon: L.divIcon({
                            html: `<div class="flag-marker"><img src="${loc.flag}"></div>`,
                            iconSize: [42, 42],
                            iconAnchor: [21, 21],
                            className: ''
                        })
                    }).addTo(map);
                });

            });
        </script>

        <?php
        return ob_get_clean();
}
add_shortcode('world_map', 'world_map_leaflet_shortcode');

/**
 * --------------------------------------------------------
 * PREMIUM EMAIL DESIGN WRAPPER
 * --------------------------------------------------------
 */
function fls_get_premium_email_template($content)
{
    $logo_url = site_url('/wp-content/uploads/2025/12/financiallifeskills-logo.png');
    $navy = '#1B2A4A';

    return "
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    </head>

    <body style='margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;'>

        <table width='100%' cellpadding='0' cellspacing='0' border='0' style='background:#f3f4f6;padding:30px 15px;'>
            <tr>
                <td align='center'>

                    <table width='100%' cellpadding='0' cellspacing='0' border='0' style='max-width:720px;background:#ffffff;border-radius:12px;overflow:hidden;'>

                        <tr>
                            <td align='center' style='background:{$navy};padding:24px;'>
                                <img src='{$logo_url}' alt='Financial Life Skills' width='170' style='display:block;'>
                            </td>
                        </tr>

                        <tr>
                            <td style='padding:40px 32px;color:#475467;font-size:16px;line-height:1.8;'>
                                {$content}
                            </td>
                        </tr>

                        <tr>
                            <td align='center' style='background:#eef1f5;padding:18px;color:#667085;font-size:12px;'>
                                © " . date('Y') . " Financial Life Skills. All rights reserved.
                            </td>
                        </tr>

                    </table>

                </td>
            </tr>
        </table>

    </body>
    </html>";
}

// WooCommerce hooks removed for B2B transition (LearnDash group trigger used instead)
// add_action('woocommerce_thankyou', 'fls_send_magic_link_welcome_email', 20, 1);
// add_action('woocommerce_payment_complete', 'fls_send_magic_link_welcome_email', 20, 1);
// add_action('woocommerce_order_status_processing', 'fls_send_magic_link_welcome_email', 20, 1);
// add_action('woocommerce_order_status_completed', 'fls_send_magic_link_welcome_email', 20, 1);

function fls_get_order_student_email($order)
{
    if (!$order) {
        return '';
    }

    $email = $order->get_meta('_billing_student_email');
    if (!$email) {
        $email = $order->get_meta('billing_student_email');
    }

    $email = sanitize_email($email);
    return is_email($email) ? $email : '';
}

function fls_get_checkout_student_email()
{
    $email = isset($_POST['billing_student_email'])
        ? sanitize_email(wp_unslash($_POST['billing_student_email']))
        : '';

    return is_email($email) ? $email : '';
}


function fls_get_magic_login_link($user, $redirect_to)
{
    if (function_exists('\\MagicLogin\\Utils\\create_login_link')) {
        return \MagicLogin\Utils\create_login_link($user, 'email', $redirect_to);
    }

    if (function_exists('create_login_link')) {
        return create_login_link($user, 'email', $redirect_to);
    }

    return false;
}

function fls_send_magic_link_welcome_email($order_id)
{
    if (!$order_id) {
        return;
    }

    if (get_post_meta($order_id, '_fls_welcome_email_sent', true)) {
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    $status = $order->get_status();
    if (in_array($status, array('failed', 'cancelled', 'refunded'), true)) {
        return;
    }

    $email = fls_get_order_student_email($order);
    if (!$email) {
        $email = $order->get_billing_email();
    }
    if (!is_email($email)) {
        return;
    }

    $user = fls_get_or_create_order_customer_user($order);
    if (!$user) {
        return;
    }

    $redirect_to = home_url('/courses/financial-life-skills');
    if (!$redirect_to) {
        $redirect_to = site_url();
    }

    $magic_link = fls_get_magic_login_link($user, $redirect_to);
    if (!$magic_link) {
        return;
    }

    $subject = 'Welcome to the Financial Life Skills program';
    $body = fls_get_magic_link_welcome_email_body($user, $email, $magic_link);
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: Financial Life Skills <seema@financiallifeskills.com>',
    );

    wp_mail($email, $subject, $body, $headers);
    update_post_meta($order_id, '_fls_welcome_email_sent', current_time('mysql'));
}

function fls_get_or_create_order_customer_user($order)
{
    $email = fls_get_order_student_email($order);
    if (!$email) {
        $email = $order->get_billing_email();
    }
    if (!is_email($email)) {
        return false;
    }

    $user = get_user_by('email', $email);
    if ($user) {
        $order->update_meta_data('_fls_student_email', $email);
        $order->save();
        return $user;
    }

    $first_name = $order->get_billing_first_name() ?: 'Student';
    $last_name = $order->get_billing_last_name();

    $username_base = sanitize_user(current(explode('@', $email)), true);
    if (empty($username_base)) {
        $username_base = 'flsuser';
    }

    $username = $username_base;
    $suffix = 1;
    while (username_exists($username)) {
        $username = $username_base . $suffix;
        $suffix++;
    }

    $password = wp_generate_password(16, true, true);
    $user_id = wp_create_user($username, $password, $email);
    if (is_wp_error($user_id)) {
        return false;
    }

    wp_update_user(array(
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
    ));

    $order->update_meta_data('_fls_student_email', $email);
    $order->save();

    return get_user_by('id', $user_id);
}

function fls_get_magic_link_welcome_email_body($user, $email, $magic_link)
{

    $display_name = $user->first_name ? $user->first_name : $user->display_name;

    $message = '<div style="font-family:Arial,Helvetica,sans-serif;color:#1f2937;line-height:1.7;max-width:650px;margin:0 auto;">';

    // Greeting
    $message .= '<p style="font-size:22px;font-weight:700;margin-bottom:20px;">
        Hi ' . esc_html($display_name) . ',
    </p>';

    // Intro
    $message .= '<p style="font-size:16px;margin-bottom:20px;">
        Welcome to the Financial Life Skills program! You now have full access and can go at your own pace.
    </p>';

    // Info Heading
    $message .= '<p style="font-size:16px;font-weight:600;margin-bottom:10px;">
        A few things to know before you begin:
    </p>';

    // Bullet Points
    $message .= '
    <ul style="font-size:16px;line-height:1.9;padding-left:22px;margin-top:0;margin-bottom:28px;">
        <li>
            The program has two parts — a Foundation Module and a Country Module — and takes around 90 minutes to complete.
        </li>

        <li>
            The budget simulator works on desktop or laptop only, so make sure you are on one when you reach that section.
        </li>

        <li>
            Your access is valid for 1 year from today.
        </li>

        <li>
            We recommend starting early and completing the country-specific module once your study destination is confirmed.
        </li>

        <li>
            If your plans change, one free country switch is included.
        </li>
    </ul>
    ';

    // Login Button
    $message .= '
    <div style="text-align:left;margin:40px 0;">
        <a href="' . esc_url($magic_link) . '" 
        style="
            display:inline-block;
            background:#0f766e;
            color:#ffffff;
            padding:18px 36px;
            border-radius:10px;
            text-decoration:none;
            font-size:16px;
            font-weight:700;
        ">
            Auto Login
        </a>
    </div>
    ';

    $message .= '<p style="font-size:16px;margin-bottom:20px;">
        This login link works once. Next time, head to <a href="https://financiallifeskills.com/" target="_blank">Financial Life Skills</a> and click the profile icon in the header to request a new link.
    </p>';

    // Registered Email
    $message .= '<p style="font-size:16px;margin-bottom:20px;">
        We sent this message to <strong>' . esc_html($email) . '</strong>.
        Keep this email address handy — you will use it every time you return to the program.
    </p>';

    // Footer
    $message .= '<p style="font-size:16px;margin-top:35px;">
        See you inside,<br>
        <strong>The Financial Life Skills Team</strong>
    </p>';

    $message .= '</div>';

    return fls_get_premium_email_template($message);
}

function fls_get_trial_magic_link_welcome_email_body($user, $email, $magic_link, $role, $start_date, $end_date)
{
    $display_name = $user->first_name ? $user->first_name : $user->display_name;
    $site_name = get_bloginfo('name');

    $message = '<div style="font-family:Arial,Helvetica,sans-serif;color:#1f2937;line-height:1.6;">';
    $message .= '<p style="font-size:1.25rem;margin-bottom:18px;">Hi ' . esc_html($display_name) . ',</p>';

    if ($role === 'student') {
        $message .= '<p style="font-size:1.08rem;margin-bottom:24px;">Welcome! Your school has set up access to the Financial Life Skills program for you. It takes about 90 minutes to complete, and you can go at your own pace.</p>';
    } else {
        $message .= '<p style="font-size:1.08rem;margin-bottom:24px;">Welcome to the Financial Life Skills program! Your preview access is now active, so you can explore what your students will learn.</p>';
    }

    if ($role === 'student') {
        $access_label = 'Your Access:';
    } else {
        $access_label = 'Your Complimentary Preview:';
    }

    $message .= '<p style="
    font-size:1.08rem;
    margin-bottom:24px;
    background:#f3f4f6;
    padding:15px;
    border-left:4px solid #0f766e;
">
<strong>' . esc_html($access_label) . '</strong><br>
From: ' . esc_html($start_date) . '<br>
To: ' . esc_html($end_date) . '
</p>';

    $message .= '<p style="text-align:left;margin:42px 0;">';
    $message .= '<a href="' . esc_url($magic_link) . '" style="display:inline-block;background:#0f766e;color:#ffffff;padding:20px 34px;border-radius:10px;text-decoration:none;font-weight:700;font-size:1.05rem;">Auto Login</a>';
    $message .= '</p>';

    $message .= '<p style="font-size:1.05rem;margin-bottom:18px;"> This login link works once. Next time, head to <a href="https://financiallifeskills.com/" target="_blank">Financial Life Skills</a> and click the profile icon in the header to request a new link.
</p>';
    $message .= '<p style="font-size:1.05rem;margin-bottom:18px;">We sent this message to <strong>' . esc_html($email) . '</strong>. Keep this email address handy — you will use it every time you return to the program.</p>';
    $message .= '<p style="font-size:1.05rem;">See you inside,<br>' . esc_html($site_name) . ' Team</p>';
    $message .= '</div>';

    return fls_get_premium_email_template($message);
}

/**
 * Shortcode to render the custom LearnDash course content design
 */
function fls_custom_course_lesson_list_shortcode($atts)
{
    // Enqueue the custom assets with dynamic versioning (Cache Busting)
    $css_path = get_stylesheet_directory() . '/learndash/assests/css/content.css';
    $js_path = get_stylesheet_directory() . '/learndash/assests/js/content.js';

    wp_enqueue_style(
        'fls-custom-course-content-css',
        get_stylesheet_directory_uri() . '/learndash/assests/css/content.css',
        array(),
        file_exists($css_path) ? filemtime($css_path) : CHILD_THEME_FINANCIAL_LIFE_SKILLS_VERSION
    );

    wp_enqueue_script(
        'fls-custom-course-content-js',
        get_stylesheet_directory_uri() . '/learndash/assests/js/content.js',
        array('jquery'),
        file_exists($js_path) ? filemtime($js_path) : CHILD_THEME_FINANCIAL_LIFE_SKILLS_VERSION,
        true
    );

    // Localize for AJAX
    wp_localize_script('fls-custom-course-content-js', 'fls_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('fls_country_selection_nonce')
    ));

    // Determine the course ID. If not passed in shortcode, try to get the current post ID
    $atts = shortcode_atts(array(
        'course_id' => get_the_ID(),
    ), $atts, 'fls_course_lesson_list');

    $course_id = intval($atts['course_id']);

    // Check if it's a valid course
    if ('sfwd-courses' !== get_post_type($course_id)) {
        return '';
    }

    // Critical Logic: If the user is NOT enrolled, hide the lesson list entirely
    // so only the theme's default sidebars (login/enroll) are visible.
    if (!sfwd_lms_has_access($course_id, get_current_user_id())) {
        return '';
    }

    ob_start();

    // Set up environment variable for the template
    set_query_var('fls_course_id', $course_id);

    // Load the template
    $template_path = get_stylesheet_directory() . '/learndash/content.php';
    if (file_exists($template_path)) {
        include($template_path);
    } else {
        echo '<p>Error: Custom course content template missing.</p>';
    }

    return ob_get_clean();
}
add_shortcode('fls_course_lesson_list', 'fls_custom_course_lesson_list_shortcode');

function fls_get_country_group_mapping()
{
    return apply_filters('fls_country_group_mapping', array(
        'GB' => 6906,
        'US' => 7098,
        'CA' => 7100,
        'AU' => 7104,
        'HK' => 7106,
        'SG' => 7108,
        'DE' => 7110,
        'NL' => 7102,
    ));
}

function fls_get_country_display_labels()
{
    return apply_filters('fls_country_display_labels', array(
        'GB' => array('UK', 'United Kingdom', 'Great Britain'),
        'US' => array('USA', 'US', 'United States', 'United States of America'),
        'CA' => array('Canada'),
        'AU' => array('Australia'),
        'HK' => array('Hong Kong'),
        'SG' => array('Singapore'),
        'DE' => array('Germany'),
        'NL' => array('Netherlands'),
    ));
}

function fls_get_course_sections($course_id)
{
    if (function_exists('learndash_30_get_course_sections')) {
        return learndash_30_get_course_sections($course_id);
    }

    $sections_json = get_post_meta($course_id, 'course_sections', true);
    if (empty($sections_json)) {
        return array();
    }

    $sections = json_decode($sections_json, true);
    return is_array($sections) ? $sections : array();
}

function fls_get_section_id($section)
{
    return is_object($section) ? intval($section->ID) : intval($section['ID'] ?? 0);
}

function fls_get_section_title($section)
{
    return is_object($section) ? (string) $section->post_title : (string) ($section['post_title'] ?? '');
}

function fls_get_section_order($section)
{
    return is_object($section) ? intval($section->order) : intval($section['order'] ?? 0);
}

function fls_get_section_steps($section)
{
    return is_object($section) ? (array) $section->steps : (array) ($section['steps'] ?? array());
}

function fls_normalize_section_title($title)
{
    $prefix = '';
    if (preg_match('/^((?:PHASE|PART|SECTION|STEP)\s*\d+)\s*[:\-\s]\s*(.+)$/i', $title, $matches)) {
        $prefix = strtoupper($matches[1]);
        $title = $matches[2];
    }

    return array($title, $prefix);
}

function fls_is_country_selection_section($title)
{
    return stripos($title, 'Your Country') !== false;
}

function fls_is_country_track_section($title)
{
    return stripos($title, 'Special country') !== false;
}

function fls_is_survey_section($title)
{
    return (
        stripos($title, 'Post Program Quick Survey') !== false
        || stripos($title, 'Quick Survey') !== false
        || stripos($title, 'Wrap-Up') !== false
        || stripos($title, 'Wrap Up') !== false
    );
}

function fls_lesson_title_country_codes($title)
{
    $matched_codes = array();

    foreach (fls_get_country_display_labels() as $code => $labels) {
        foreach ((array) $labels as $label) {
            if (preg_match('/(^|[^A-Za-z0-9])' . preg_quote($label, '/') . '([^A-Za-z0-9]|$)/i', $title)) {
                $matched_codes[] = $code;
                break;
            }
        }
    }

    return array_values(array_unique($matched_codes));
}

function fls_lesson_matches_selected_country($title, $selected_country_code)
{
    $matched_codes = fls_lesson_title_country_codes($title);

    if (empty($matched_codes)) {
        return true;
    }

    return in_array($selected_country_code, $matched_codes, true);
}

function fls_get_course_lessons($course_id, $user_id)
{
    if (!function_exists('learndash_get_course_lessons_list')) {
        return array();
    }

    $lessons = learndash_get_course_lessons_list($course_id, $user_id, array('num' => -1));
    return is_array($lessons) ? $lessons : array();
}

function fls_get_lesson_post_from_item($lesson_item)
{
    if (is_object($lesson_item) && isset($lesson_item->ID)) {
        return $lesson_item;
    }

    if (is_array($lesson_item) && isset($lesson_item['post']) && is_object($lesson_item['post'])) {
        return $lesson_item['post'];
    }

    return null;
}

function fls_get_foundation_completion_lesson_id($course_id, $user_id = 0)
{
    $default_lesson_id = (int) apply_filters('fls_default_foundation_completion_lesson_id', 6892, $course_id, $user_id);

    $sections = fls_get_course_sections($course_id);
    if (empty($sections)) {
        return $default_lesson_id;
    }

    $phase_two_order = null;
    foreach ($sections as $section) {
        $title = fls_get_section_title($section);
        if (fls_is_country_selection_section($title) || fls_is_country_track_section($title) || fls_is_survey_section($title)) {
            $order = fls_get_section_order($section);
            $phase_two_order = is_null($phase_two_order) ? $order : min($phase_two_order, $order);
        }
    }

    if (is_null($phase_two_order)) {
        return $default_lesson_id;
    }

    $candidate_lesson_id = 0;
    foreach ($sections as $section) {
        if (fls_get_section_order($section) >= $phase_two_order) {
            continue;
        }

        foreach (fls_get_section_steps($section) as $step_id) {
            if ('sfwd-lessons' === get_post_type((int) $step_id)) {
                $candidate_lesson_id = (int) $step_id;
            }
        }
    }

    return $candidate_lesson_id ? $candidate_lesson_id : $default_lesson_id;
}
/**
 * Handle Country Selection AJAX
 */
function fls_handle_country_selection()
{
    check_ajax_referer('fls_country_selection_nonce', 'nonce');

    $user_id = get_current_user_id();
    $country_name = sanitize_text_field($_POST['country_name']);
    $country_code = sanitize_text_field($_POST['country_code']);
    $course_id = intval($_POST['course_id']);

    if (!$user_id || !$country_name) {
        wp_send_json_error(array('message' => 'Invalid data provided.'));
    }

    // Save Selection to User Meta
    update_user_meta($user_id, 'fls_selected_country_name', $country_name);
    update_user_meta($user_id, 'fls_selected_country_code', $country_code);

    // Track Country Change Count & History
    $change_count = (int) get_user_meta($user_id, 'fls_country_change_count', true);
    $change_count++;
    update_user_meta($user_id, 'fls_country_change_count', $change_count);

    $history = get_user_meta($user_id, 'fls_country_change_history', true);
    if (!is_array($history)) {
        $history = array();
    }
    $history[] = array(
        'country_name' => $country_name,
        'country_code' => $country_code,
        'timestamp'    => current_time('mysql'),
        'count'        => $change_count,
    );
    update_user_meta($user_id, 'fls_country_change_history', $history);

    $group_mapping = fls_get_country_group_mapping();

    if (isset($group_mapping[$country_code])) {
        $group_id = $group_mapping[$country_code];
        ld_update_group_access($user_id, $group_id, false);
    }

    // Reset progress across all subsequent lessons when country is selected or updated
    fls_reset_country_specific_progress($user_id, $course_id);

    $user_info = get_userdata($user_id);
    $course_title = get_the_title($course_id);
    $has_switched = get_user_meta($user_id, 'fls_has_switched_country', true);
    $display_name = $user_info->first_name ? $user_info->first_name : $user_info->display_name;

    // 1. Notify Admin
    $admin_email = get_option('admin_email');
    $admin_subject = ($has_switched === '1' ? "Updated" : "New") . " Country Selection: " . $user_info->display_name;

    /*
    |--------------------------------------------------------------------------
    | Parent Details
    |--------------------------------------------------------------------------
    */

    $parent_name = get_user_meta($user_id, 'billing_first_name', true) . ' ' . get_user_meta($user_id, 'billing_last_name', true);

    $parent_email = get_user_meta($user_id, 'billing_email', true);

    $parent_phone = get_user_meta($user_id, 'billing_phone', true);

    /*
    |--------------------------------------------------------------------------
    | Admin Email Message
    |--------------------------------------------------------------------------
    */

    $admin_message = "
    Hello Admin,<br><br>

    User <strong>" . esc_html($user_info->display_name) . "</strong>
    (" . esc_html($user_info->user_email) . ")
    has " . ($has_switched === '1' ? 'updated' : 'selected') . "
    their destination to
    <strong>" . esc_html($country_name) . "</strong>.<br><br>

    <strong>Course:</strong> " . esc_html($course_title) . "<br><br>

    <hr>

    <h3>Parent Details</h3>

    <strong>Parent Name:</strong>
    " . esc_html(trim($parent_name)) . "<br>

    <strong>Parent Email:</strong>
    " . esc_html($parent_email) . "<br>

    <strong>Parent Phone:</strong>
    " . esc_html($parent_phone) . "<br><br>

    Best Regards.
    ";

    $headers = array(
        'Content-Type: text/html; charset=UTF-8'
    );

    wp_mail($admin_email, $admin_subject, $admin_message, $headers);

    // 2. Notify User (Premium HTML Template)
    $navy = '#1B2A4A';
    $green = '#3EAA6D';
    $logo_url = site_url('/wp-content/uploads/2025/12/financiallifeskills-logo.png');

    if ($has_switched === '1') {
        // Updated email
        $redirect_to = home_url('/courses/financial-life-skills');
        $magic_link = fls_get_magic_login_link($user_info, $redirect_to);

        $user_subject = "Confirmation: Country updated";
        $content = "
            <p>Hi {$display_name},</p>
            <p>We have received your new country selection. You have changed your destination to <strong>{$country_name}</strong> and your country-specific lessons are now ready inside the Financial Life Skills program.</p>
            <p>A quick note: you've now used your one free destination switch, so <strong>{$country_name}</strong> is now set as your final destination on the program.</p>
            <div style='margin: 40px 0; border-top: 1px solid #eee; padding-top: 20px;'>
                <strong>Auto Login:</strong><br>
                <a href='{$magic_link}' style='display:inline-block; background-color:{$green}; color:#ffffff; padding:16px 32px; border-radius:8px; text-decoration:none; font-weight:700; margin: 15px 0;'>Login to Your Course</a>
                <p style='font-size: 12px; color: #718096;'>Login link will expire in 15 minute(s).</p>
            </div>
            <p>See you inside,<br>The Financial Life Skills Team</p>
        ";
    } else {
        // Initial Selection email
        $redirect_to = home_url('/courses/financial-life-skills');
        $magic_link = fls_get_magic_login_link($user_info, $redirect_to);
        $user_subject = "Confirmation: Country selected";
        $content = "
            <p>Hi {$display_name},</p>
            <p>We've received your selection. You have chosen <strong>{$country_name}</strong> as your destination.</p>
            <p>Your country-specific lessons are now ready for you.</p>
            <div style='margin: 40px 0; border-top: 1px solid #eee; padding-top: 20px;'>
                <strong>Auto Login:</strong><br>
                <a href='{$magic_link}' style='display:inline-block; background-color:{$green}; color:#ffffff; padding:16px 32px; border-radius:8px; text-decoration:none; font-weight:700; margin: 15px 0;'>Login to Your Course</a>
                <p style='font-size: 12px; color: #718096;'>Login link will expire in 15 minute(s).</p>
            </div>
            <p>See you inside,<br>The Financial Life Skills Team</p>
        ";
    }

    $user_message = "
    <html>
    <head>
        <style>
            .email-body { font-family: 'DM Sans', sans-serif; background-color: #f7f9fc; padding: 40px 0; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
            .header { background-color: {$navy}; padding: 30px; text-align: center; }
            .content { padding: 40px; text-align: left; color: #1A202C; }
            .footer { background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #718096; }
            p { font-size: 16px; line-height: 1.6; color: #4A5568; margin-bottom: 20px; }
        </style>
    </head>
    <body class='email-body'>
        <div class='container'>
            <div class='header'>
                <img src='{$logo_url}' alt='Financial Life Skills' width='180'>
            </div>
            <div class='content'>
                {$content}
            </div>
            <div class='footer'>
                © " . date('Y') . " Financial Life Skills. All rights reserved.
            </div>
        </div>
    </body>
    </html>";

    // Removed as per client request (PDF)
    // wp_mail(
    //     $user_info->user_email,
    //     $user_subject,
    //     $user_message,
    //     ['Content-Type: text/html; charset=UTF-8']
    // );


    wp_send_json_success(array('message' => 'Selection saved and notifications sent.'));
}
add_action('wp_ajax_fls_select_country', 'fls_handle_country_selection');
add_action('wp_ajax_nopriv_fls_select_country', 'fls_handle_country_selection');

/**
 * AJAX Header: Reset Country Selection (Switch Once)
 */
function fls_ajax_reset_country()
{
    check_ajax_referer('fls_country_selection_nonce', 'nonce');

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(array('message' => 'User not logged in.'));
    }

    $current_code = get_user_meta($user_id, 'fls_selected_country_code', true);


    $group_mapping = fls_get_country_group_mapping();
    if ($current_code && isset($group_mapping[$current_code])) {
        ld_update_group_access($user_id, $group_mapping[$current_code], true);
    }

    // Clear Meta
    delete_user_meta($user_id, 'fls_selected_country_name');
    delete_user_meta($user_id, 'fls_selected_country_code');

    // Mark as switched
    update_user_meta($user_id, 'fls_has_switched_country', '1');

    // Reset progress across all subsequent lessons
    $course_id = 4137;
    fls_reset_country_specific_progress($user_id, $course_id);

    wp_send_json_success(array('message' => 'Selection reset. You can now choose a new country.'));
}
add_action('wp_ajax_fls_reset_country', 'fls_ajax_reset_country');

/**
 * Reset all LearnDash progress for lessons occurring after the Foundation section.
 * This ensures the user must go through the new country track step-by-step.
 */
function fls_reset_country_specific_progress($user_id, $course_id)
{
    if (!function_exists('learndash_get_course_lessons_list')) {
        return;
    }

    $all_lessons = fls_get_course_lessons($course_id, $user_id);
    $past_foundation = false;

    if (empty($all_lessons) || !is_array($all_lessons)) {
        return;
    }

    foreach ($all_lessons as $lesson_item) {
        $lesson = fls_get_lesson_post_from_item($lesson_item);
        if (!$lesson)
            continue;

        $lesson_id = $lesson->ID;

        if ($past_foundation) {
            learndash_process_mark_incomplete($user_id, $course_id, $lesson_id, false);

            // Unmark topics
            if (function_exists('learndash_course_get_topics')) {
                $topics = learndash_course_get_topics($course_id, $lesson_id);
                if (!empty($topics)) {
                    foreach ($topics as $topic) {
                        $topic_id = is_object($topic) ? $topic->ID : (isset($topic['post']) ? $topic['post']->ID : 0);
                        if ($topic_id) {
                            learndash_process_mark_incomplete($user_id, $course_id, $topic_id, false);

                            // Unmark quizzes in topic
                            if (function_exists('learndash_get_lesson_quiz_list')) {
                                $topic_quizzes = learndash_get_lesson_quiz_list($topic_id, $user_id, $course_id);
                                if (!empty($topic_quizzes)) {
                                    foreach ($topic_quizzes as $quiz) {
                                        $q_id = is_object($quiz['post']) ? $quiz['post']->ID : 0;
                                        if ($q_id)
                                            learndash_process_mark_incomplete($user_id, $course_id, $q_id, false);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Unmark quizzes in lesson
            if (function_exists('learndash_get_lesson_quiz_list')) {
                $lesson_quizzes = learndash_get_lesson_quiz_list($lesson_id, $user_id, $course_id);
                if (!empty($lesson_quizzes)) {
                    foreach ($lesson_quizzes as $quiz) {
                        $q_id = is_object($quiz['post']) ? $quiz['post']->ID : 0;
                        if ($q_id)
                            learndash_process_mark_incomplete($user_id, $course_id, $q_id, false);
                    }
                }
            }
        }

        // 6892 is the Foundation Completion Lesson
        if ($lesson_id == fls_get_foundation_completion_lesson_id($course_id, $user_id)) {
            $past_foundation = true;
        }
    }
}

/**
 * Shortcode: [fls_country_selection_form]
 * Renders the country selection card and hides the Next Lesson button until submitted.
 */
function fls_country_selection_form_shortcode()
{
    $user_id = get_current_user_id();
    if (!$user_id)
        return '';

    // Enqueue assets with dynamic versioning (Cache Busting)
    $css_path = get_stylesheet_directory() . '/learndash/assests/css/content.css';
    $js_path = get_stylesheet_directory() . '/learndash/assests/js/content.js';

    wp_enqueue_style(
        'fls-custom-course-content-css',
        get_stylesheet_directory_uri() . '/learndash/assests/css/content.css',
        array(),
        file_exists($css_path) ? filemtime($css_path) : CHILD_THEME_FINANCIAL_LIFE_SKILLS_VERSION
    );

    wp_enqueue_script(
        'fls-custom-course-content-js',
        get_stylesheet_directory_uri() . '/learndash/assests/js/content.js',
        array('jquery'),
        file_exists($js_path) ? filemtime($js_path) : CHILD_THEME_FINANCIAL_LIFE_SKILLS_VERSION,
        true
    );

    // Localize for AJAX (if not already localized)
    wp_localize_script('fls-custom-course-content-js', 'fls_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('fls_country_selection_nonce')
    ));

    $selected_country = get_user_meta($user_id, 'fls_selected_country_name', true);
    $is_country_selected = !empty($selected_country);

    // Determine current Course ID and Lesson ID
    $lesson_id = get_the_ID();
    $course_id = learndash_get_course_id($lesson_id);
    if (!$course_id)
        $course_id = 4137;

    // Check if THIS lesson is complete. If not, don't show the form yet.
    if (!learndash_is_lesson_complete($user_id, $lesson_id, $course_id)) {
        return ''; // Or return a message like "Finish the lesson to select your country."
    }

    /*
    |--------------------------------------------------------------------------
    | ONLY trigger on Foundation Completion Lesson
    |--------------------------------------------------------------------------
    */

    $foundation_completion_lesson_id = fls_get_foundation_completion_lesson_id($course_id, $user_id);

    if ($lesson_id != $foundation_completion_lesson_id) {
        return '';
    }

    ob_start();

    // Hide Next Lesson button if not selected
    if (!$is_country_selected) {
        echo '<style>.ld-navigation__next { display: none !important; }</style>';
    }
    ?>
        <div id="fls-country-selection-container" class="fls-selection-card fls-lesson-embedded-form"
            data-course-id="<?php echo $course_id; ?>">
            <div class="fls-card-header">
                <div class="fls-icon-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="2" y1="12" x2="22" y2="12" />
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                    </svg>
                </div>
                <div class="fls-header-text">
                    <h3>Where are you heading?</h3>
                    <p>Select your destination to unlock country-specific lessons — banking, budgeting with real costs, and tax
                        guides tailored to where you are going.</p>
                </div>
            </div>

            <div class="fls-country-grid">
                <?php
                $countries = array(
                    'GB' => 'United Kingdom',
                    'US' => 'United States',
                    'CA' => 'Canada',
                    'AU' => 'Australia',
                    'HK' => 'Hong Kong',
                    'SG' => 'Singapore',
                    'DE' => 'Germany',
                    'NL' => 'Netherlands'
                );
                foreach ($countries as $code => $name):
                    $active_class = ($selected_country == $name) ? 'is-active' : '';
                    ?>
                        <div class="fls-country-tile <?php echo $active_class; ?>" data-code="<?php echo esc_attr($code); ?>"
                            data-name="<?php echo esc_attr($name); ?>">
                            <div class="fls-tile-code"><?php echo esc_html($code); ?></div>
                            <div class="fls-tile-name"><?php echo esc_html($name); ?></div>
                        </div>
                <?php endforeach; ?>
            </div>

            <div class="fls-card-footer">
                <?php if ($is_country_selected): ?>
                        <div class="fls-selection-status">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Selection Confirmed: <strong><?php echo esc_html($selected_country); ?></strong>. Click 'Back to
                                Course' to continue with your country module.</span>
                        </div>
                <?php else: ?>
                        <button type="button" id="fls-country-confirm-btn" class="fls-primary-btn" disabled>Confirm Selection &
                            Proceed</button>
                        <p class="fls-footer-note">Choose carefully. This will unlock your specific learning track.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
}
add_shortcode('fls_country_selection_form', 'fls_country_selection_form_shortcode');

/**
 * Surgical CSS Fix - Updated for Guest Visibility
 * Ye code sirf Students ke liye extra cheezon ko hide karega, 
 * guests ko feature image aur enroll button sahi se dikhenge.
 */
add_action('wp_head', function () {
    if (is_singular(array('sfwd-courses', 'sfwd-lessons'))) {
        ?>
                <style id="fls-surgical-fix">
                    /* SIRF tab hide karein jab user ke paas ACCESS ho (Student View) */
                    .user_has_access .ld-accordion.ld-accordion--course,
                    .user_has_access .ld-main-course-resume,
                    .user_has_access .ld-course-status,
                    .user_has_access .ld-layout__header,
                    .user_has_access .ld-featured-image {
                        display: none !important;
                    }

                    .learndash-wrapper {
                        row-gap: 0px !important;
                    }

                    /* Lesson navigation should remain visible for students with access. */
                    /* Removed the previous hide rule to prevent blank/hidden lesson content for non-admin learners. */

                    /* Guest Page (No Access) par feature image ko dikhai dena chahiye */
                    .user_has_no_access .ld-featured-image {
                        display: block !important;
                    }

                    /* Custom Dashboard hamesha sahi dikhna chahiye */
                    .fls-course-wrapper {
                        display: block !important;
                    }
                </style>
                <?php
    }
}, 100);

/**
 * Allow Google Apps Script iframe content inside LearnDash lessons.
 * This removes any X-Frame-Options header and adds a minimal frame-src/child-src policy.
 */
function fls_allow_google_apps_script_iframe_headers($headers)
{
    if (!is_singular(array('sfwd-courses', 'sfwd-lessons'))) {
        return $headers;
    }
    $allowed_sources = array(
        'https://docs.google.com',
        'https://docs.google.com/forms',
        'https://script.google.com',
        'https://script.googleusercontent.com',
        'https://iframe.bunny.net',
        'https://play.bunny.net',
        'https://player.bunnycdn.com',
        'https://mediadelivery.net',
        'https://*.mediadelivery.net',
        'https://*.googleusercontent.com',
    );
    $allowed = implode(' ', $allowed_sources);
    $directive = "frame-src {$allowed}; child-src {$allowed};";

    if (!empty($headers['Content-Security-Policy'])) {
        $existing = $headers['Content-Security-Policy'];

        if (stripos($existing, 'frame-src') === false) {
            $existing .= '; ' . $directive;
        } else {
            foreach (array('frame-src', 'child-src') as $rule) {
                if (stripos($existing, $rule) === false) {
                    $existing .= '; ' . $rule . ' ' . $allowed;
                } else {
                    $existing = preg_replace_callback(
                        '/(' . preg_quote($rule, '/') . ')[^;]*/i',
                        function ($matches) use ($allowed_sources) {
                            $tokens = preg_split('/\s+/', trim($matches[0]));
                            foreach ($allowed_sources as $src) {
                                if (!in_array($src, $tokens, true)) {
                                    $tokens[] = $src;
                                }
                            }
                            return implode(' ', array_unique($tokens));
                        },
                        $existing
                    );
                }
            }
        }

        $headers['Content-Security-Policy'] = $existing;
    } else {
        $headers['Content-Security-Policy'] = $directive;
    }

    if (isset($headers['X-Frame-Options'])) {
        unset($headers['X-Frame-Options']);
    }

    return $headers;
}
add_filter('wp_headers', 'fls_allow_google_apps_script_iframe_headers');
add_action('send_headers', 'fls_allow_google_apps_script_iframe_send_headers', 1);

function fls_allow_google_apps_script_iframe_send_headers()
{
    if (!is_singular(array('sfwd-courses', 'sfwd-lessons'))) {
        return;
    }

    if (!headers_sent()) {
        header_remove('X-Frame-Options');
    }
}

/**
 * Force LearnDash lesson content containers to remain visible.
 * This helps when content is accidentally hidden by CSS on student lesson pages.
 */
add_action('wp_head', function () {
    if (is_singular('sfwd-lessons')) {
        echo "<style>
            .learndash-wrapper .ld-lesson-content,
            .learndash-wrapper .ld-lesson-content *,
            .learndash-wrapper .ld-content {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
        </style>";
    }
}, 101);

/**
 * PREMIUM BRANDED EMAIL NOTIFICATIONS
 * Replaces default WordPress system emails with high-end HTML versions.
 */

// 1. Set global email content type to HTML
add_filter('wp_mail_content_type', function () {
    return 'text/html';
});

// 2. Set 'From' name and email to be professional
add_filter('wp_mail_from', function ($email) {
    return get_option('admin_email');
});
add_filter('wp_mail_from_name', function ($name) {
    return 'Financial Life Skills';
});

// 3. Customize the New User Registration Email
add_filter('wp_new_user_notification_email', 'fls_custom_new_user_notification_email', 10, 3);

function fls_custom_new_user_notification_email($wp_new_user_notification_email, $user, $blogname)
{
    $key = get_password_reset_key($user);
    $reset_url = site_url("/set-password/?action=rp&key=$key&login=" . rawurlencode($user->user_login));
    $logo_url = site_url('/wp-content/uploads/2025/12/financiallifeskills-logo.png');

    // Header & Footer Colors
    $navy = '#1B2A4A';
    $green = '#3EAA6D';

    $message = "
    <html>
    <head>
        <style>
            .email-body { font-family: 'DM Sans', sans-serif; background-color: #f7f9fc; padding: 40px 0; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
            .header { background-color: {$navy}; padding: 30px; text-align: center; }
            .content { padding: 40px; text-align: center; color: #1A202C; }
            .footer { background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #718096; }
            h2 { font-size: 24px; margin-bottom: 20px; color: {$navy}; }
            p { font-size: 16px; line-height: 1.6; color: #4A5568; margin-bottom: 30px; }
            .button { background-color: {$green}; color: #ffffff !important; padding: 16px 32px; border-radius: 8px; text-decoration: none !important; font-weight: 700; display: inline-block; }
            .meta { margin-top: 30px; font-size: 14px; opacity: 0.8; }
        </style>
    </head>
    <body class='email-body'>
        <div class='container'>
            <div class='header'>
                <img src='{$logo_url}' alt='Financial Life Skills' width='180'>
            </div>
            <div class='content'>
                <h2>Verification Required</h2>
                <p>Welcome to <strong>Financial Life Skills</strong>! To get started on your journey, please log in using your registered email address on the Welcome Back page.</p>
                <a href='/welcome-back/' class='button'>Login Now</a>
                <div class='meta'>
                    <strong>Username:</strong> {$user->user_login}
                </div>
            </div>
            <div class='footer'>
                © " . date('Y') . " Financial Life Skills. All rights reserved.<br>
                Empowering your financial future through education.
            </div>
        </div>
    </body>
    </html>";

    $wp_new_user_notification_email['message'] = $message;
    $wp_new_user_notification_email['subject'] = "Welcome to Financial Life Skills - Set Your Password";
    $wp_new_user_notification_email['headers'] = array('Content-Type: text/html; charset=UTF-8');

    return $wp_new_user_notification_email;
}

/**
 * DIRECT CHECKOUT FOR LEARNDASH ENROLLMENT
 * Skips the single product page and goes straight to checkout.
 */
add_filter('learndash_payment_buttons', 'fls_direct_checkout_enrollment_button', 20, 2);
function fls_direct_checkout_enrollment_button($buttons, $course_id)
{
    // Regex to find the product link in the button HTML
    if (preg_match('/href="([^"]+product\/([^"\/]+)[^"]*)"/', $buttons, $matches)) {
        $product_url = $matches[1];
        $product_slug = $matches[2];

        // Find the product ID by slug
        $product = get_page_by_path($product_slug, OBJECT, 'product');

        if ($product) {
            // Build the direct checkout URL: checkout/?add-to-cart=ID
            $checkout_url = add_query_arg('add-to-cart', $product->ID, wc_get_checkout_url());
            // Replace the URL in the button HTML
            $buttons = str_replace($product_url, $checkout_url, $buttons);
        }
    }
    return $buttons;
}

/**
 * REDIRECT TO CHECKOUT AFTER ADD TO CART
 * Ensures any 'Add to Cart' action lands on Checkout immediately.
 */
add_filter('woocommerce_add_to_cart_redirect', function () {
    return wc_get_checkout_url();
});

/**
 * BYPASS SINGLE PRODUCT PAGE REDIRECT
 * If a user manually visits the product page for a course, take them to checkout instead.
 */
add_action('template_redirect', 'fls_skip_single_product_page_for_courses');
function fls_skip_single_product_page_for_courses()
{
    if (is_product() && !is_admin()) {
        global $post;
        // Check if this product is linked in the 'Course' category
        if (has_term('course', 'product_cat', $post->ID)) {
            wp_safe_redirect(add_query_arg('add-to-cart', $post->ID, wc_get_checkout_url()));
            exit;
        }
    }
}



// 1. LearnDash login page → custom page redirect
add_filter('learndash_login_url', function () {
    return home_url('/get-started');
});

// 2. WP default login page redirect
add_action('init', function () {
    $page = $_SERVER['REQUEST_URI'];
    if (strpos($page, 'wp-login.php') !== false && !is_admin()) {
        // Check returning user cookie
        if (isset($_COOKIE['ld_returning_user'])) {
            wp_redirect(home_url('/welcome-back'));
        } else {
            wp_redirect(home_url('/get-started'));
        }
        exit;
    }
});

// 3. Set cookie when user logs out (mark as returning)
add_action('wp_logout', function () {

    setcookie('ld_returning_user', '1', time() + (365 * DAY_IN_SECONDS), '/');

    if (!headers_sent()) {

        wp_safe_redirect(home_url('/welcome-back/'));
        exit;
    }
});

// 4. After login → LearnDash dashboard
add_filter('login_redirect', function ($redirect_to, $request, $user) {
    if (!is_wp_error($user) && isset($user->roles)) {
        return home_url('/courses/financial-life-skills'); // apna LD dashboard URL yahan
    }
    return $redirect_to;
}, 10, 3);

// 5. Already logged in users ko login page pe na jane do
add_action('template_redirect', function () {
    if (is_user_logged_in() && (is_page('get-started') || is_page('welcome-back'))) {
        wp_safe_redirect(home_url('/courses/financial-life-skills'));
        exit;
    }
});

add_filter('magic_login_error_message', function ($message, $context) {
    if ('invalid_user' === $context) {
        return __('We don\'t recognise this email — please use the address you used when you signed up.', 'financial-life-skills');
    }

    if ('invalid_token' === $context) {
        return __('This login link is invalid or expired. Please request a new link and try again.', 'financial-life-skills');
    }

    return $message;
}, 10, 2);

/**
 * GENERATE FLAT ORDERED ARRAY OF VALID LESSON IDS FOR A USER
 * Mirrors the exact filtering logic from content.php
 */
function fls_get_valid_lesson_sequence($course_id, $user_id)
{
    $sequence = array();

    // Check if Phase 1 (Foundation) is complete
    $foundation_lesson_id = fls_get_foundation_completion_lesson_id($course_id, $user_id);
    $is_foundation_complete = learndash_is_lesson_complete($user_id, $foundation_lesson_id, $course_id);

    // Selected country
    $selected_country_code = get_user_meta($user_id, 'fls_selected_country_code', true);
    $is_country_selected = (
        !empty($selected_country_code)
        && !empty(get_user_meta($user_id, 'fls_selected_country_name', true))
    );
    // Extra safety check
    $valid_country_codes = array_keys(fls_get_country_group_mapping());

    if (
        $is_country_selected &&
        !in_array($selected_country_code, $valid_country_codes, true)
    ) {
        $is_country_selected = false;
    }

    $lessons = fls_get_course_lessons($course_id, $user_id);

    // Fetch Sections
    $sections = fls_get_course_sections($course_id);

    $grouped_lessons = array();
    $grouped_lessons[0] = array(
        'title' => "Course Introduction",
        'lessons' => array(),
    );

    if (!empty($sections) && is_array($sections)) {
        foreach ($sections as $section) {
            $sec_id = fls_get_section_id($section);
            if (!$sec_id)
                continue;
            $title = fls_get_section_title($section);
            list($title) = fls_normalize_section_title($title);

            $grouped_lessons[$sec_id] = array(
                'title' => $title,
                'lessons' => array(),
            );
        }
    }

    foreach ($lessons as $lesson_item) {
        $lesson = fls_get_lesson_post_from_item($lesson_item);
        if (!$lesson)
            continue;

        $lesson_id = $lesson->ID;
        $assigned_sec_id = 0;

        if (!empty($sections)) {
            foreach ($sections as $section) {
                $sec_id = fls_get_section_id($section);
                $sec_steps = fls_get_section_steps($section);
                if (in_array($lesson_id, (array) $sec_steps) || in_array((string) $lesson_id, (array) $sec_steps)) {
                    $assigned_sec_id = $sec_id;
                    break;
                }
            }
        }

        if (!isset($grouped_lessons[$assigned_sec_id])) {
            $assigned_sec_id = 0;
        }

        $grouped_lessons[$assigned_sec_id]['lessons'][] = array(
            'id' => $lesson_id,
            'title' => $lesson->post_title,
        );
    }

    $final_sections = array();

    if (!empty($grouped_lessons[0]['lessons'])) {
        $g = $grouped_lessons[0];
        $g['id'] = 0;
        $final_sections[] = $g;
    }

    $section_order = array();
    if (!empty($sections)) {
        foreach ($sections as $section) {
            $sec_id = fls_get_section_id($section);
            $order = fls_get_section_order($section);
            $section_order[$sec_id] = $order;
        }
        asort($section_order);
    }

    foreach ($section_order as $sid => $order) {
        $exists = isset($grouped_lessons[$sid]);
        $title = $exists ? $grouped_lessons[$sid]['title'] : 'UNKNOWN';
        if ($exists) {
            $is_target = (
                fls_is_country_selection_section($title)
                || fls_is_country_track_section($title)
                || fls_is_survey_section($title)
            );
            if (!empty($grouped_lessons[$sid]['lessons']) || $is_target) {
                $g = $grouped_lessons[$sid];
                $g['id'] = $sid;
                $final_sections[] = $g;
            }
        }
    }

    // Now flatten loop checking conditions
    foreach ($final_sections as $group) {
        $is_country_section = fls_is_country_selection_section($group['title']);
        $is_special_section = fls_is_country_track_section($group['title']);
        $is_survey_section = fls_is_survey_section($group['title']);

        if (($is_country_section || $is_special_section || $is_survey_section) && !$is_foundation_complete)
            continue;
        if ($is_special_section && !$is_country_selected)
            continue;
        if ($is_country_section && $is_country_selected)
            continue;
        if ($is_survey_section && !$is_country_selected)
            continue;

        foreach ($group['lessons'] as $lesson) {
            if ($is_special_section) {
                $title = $lesson['title'];
                if (!fls_lesson_matches_selected_country($title, $selected_country_code)) {
                    continue;
                }
            }
            $sequence[] = $lesson['id'];
        }
    }

    return $sequence;
}

/**
 * Debug: Log when lesson content is hidden for a non-admin student.
 */
add_filter('learndash_model_lesson_is_content_visible', 'fls_debug_learndash_lesson_visibility', 10, 3);
function fls_debug_learndash_lesson_visibility($is_visible, $lesson, $user)
{
    if (!is_singular('sfwd-lessons')) {
        return $is_visible;
    }

    $current_user_id = get_current_user_id();
    if (empty($current_user_id)) {
        return $is_visible;
    }

    $user_id = is_object($user) ? $user->ID : intval($user);
    if ($user_id !== $current_user_id) {
        return $is_visible;
    }

    if (current_user_can('manage_options')) {
        return $is_visible;
    }

    if ($is_visible) {
        return $is_visible;
    }

    $lesson_id = method_exists($lesson, 'get_id') ? $lesson->get_id() : 0;
    $course_id = learndash_get_course_id($lesson_id);
    $has_access = sfwd_lms_has_access($lesson_id, $current_user_id) ? 'yes' : 'no';
    $progression = learndash_lesson_progression_enabled($course_id) ? 'on' : 'off';
    $previous_step_id = learndash_user_progress_get_previous_incomplete_step($current_user_id, $course_id, $lesson_id);
    $completed = learndash_user_progress_is_step_complete($current_user_id, $course_id, $lesson_id) ? 'yes' : 'no';

    error_log(sprintf(
        'FLS DEBUG: lesson_visibility hidden lesson_id=%d user_id=%d course_id=%d has_access=%s progression=%s previous_incomplete=%s completed=%s',
        $lesson_id,
        $current_user_id,
        $course_id,
        $has_access,
        $progression,
        $previous_step_id ? $previous_step_id : 'none',
        $completed
    ));

    return $is_visible;
}

/**
 * Debug shortcode to display the custom LearnDash lesson progression sequence.
 */
function fls_lesson_progress_debug_shortcode($atts)
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return '<p>Please login to view lesson progression debug details.</p>';
    }

    $atts = shortcode_atts(
        array(
            'course_id' => 0,
        ),
        $atts,
        'fls_lesson_progress_debug'
    );

    $course_id = intval($atts['course_id']);
    if (!$course_id) {
        $course_id = learndash_get_course_id(get_the_ID());
    }
    if (!$course_id) {
        return '<p>Course ID could not be determined.</p>';
    }

    $lesson_id = get_the_ID();
    $sequence = fls_get_valid_lesson_sequence($course_id, $user_id);
    $previous_incomplete = learndash_user_progress_get_previous_incomplete_step($user_id, $course_id, $lesson_id);
    $lesson_complete = learndash_user_progress_is_step_complete($user_id, $course_id, $lesson_id) ? 'yes' : 'no';
    $has_access = sfwd_lms_has_access($lesson_id, $user_id) ? 'yes' : 'no';

    ob_start();
    ?>
        <div class="fls-lesson-progress-debug"
            style="background:#fff;border:1px solid #ddd;padding:16px;margin:16px 0;font-family:Arial, sans-serif;">
            <h3 style="margin-top:0;">FLS Lesson Progression Debug</h3>
            <p><strong>User ID:</strong> <?php echo esc_html($user_id); ?></p>
            <p><strong>Course ID:</strong> <?php echo esc_html($course_id); ?></p>
            <p><strong>Lesson ID:</strong> <?php echo esc_html($lesson_id); ?></p>
            <p><strong>Has Access:</strong> <?php echo esc_html($has_access); ?></p>
            <p><strong>Lesson Complete:</strong> <?php echo esc_html($lesson_complete); ?></p>
            <p><strong>Previous Incomplete Step:</strong>
                <?php echo esc_html($previous_incomplete ? $previous_incomplete : 'none'); ?></p>
            <h4 style="margin-bottom:8px;">Custom Valid Sequence</h4>
            <ol style="margin:0;padding-left:20px;">
                <?php foreach ($sequence as $seq_id): ?>
                        <li><?php echo esc_html(get_the_title($seq_id) . ' (' . $seq_id . ')'); ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
        <?php
        return ob_get_clean();
}
add_shortcode('fls_lesson_progress_debug', 'fls_lesson_progress_debug_shortcode');

add_action('wp_footer', 'fls_lesson_progress_footer_debug');
function fls_lesson_progress_footer_debug()
{
    if (!is_singular('sfwd-lessons')) {
        return;
    }

    if (empty($_GET['fls_debug'])) {
        return;
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $lesson_id = get_the_ID();
    $course_id = learndash_get_course_id($lesson_id);
    $sequence = fls_get_valid_lesson_sequence($course_id, $user_id);
    $previous_incomplete = learndash_user_progress_get_previous_incomplete_step($user_id, $course_id, $lesson_id);
    $lesson_complete = learndash_user_progress_is_step_complete($user_id, $course_id, $lesson_id) ? 'yes' : 'no';
    $has_access = sfwd_lms_has_access($lesson_id, $user_id) ? 'yes' : 'no';

    echo '<div style="position:fixed;bottom:0;left:0;right:0;z-index:99999;background:#111;color:#fff;padding:12px;font-family:Arial, sans-serif;line-height:1.4em;max-height:50vh;overflow:auto;box-shadow:0 -2px 10px rgba(0,0,0,.4);">';
    echo '<strong>FLS Lesson Progression Debug</strong><br/>';
    echo 'User ID: ' . esc_html($user_id) . ' | Course ID: ' . esc_html($course_id) . ' | Lesson ID: ' . esc_html($lesson_id) . '<br/>';
    echo 'Has Access: ' . esc_html($has_access) . ' | Complete: ' . esc_html($lesson_complete) . ' | Previous Incomplete: ' . esc_html($previous_incomplete ? $previous_incomplete : 'none') . '<br/>';
    echo '<strong>Valid Sequence:</strong><br/>';
    echo '<ol style="margin:0;padding-left:18px;">';
    foreach ($sequence as $seq_id) {
        echo '<li>' . esc_html(get_the_title($seq_id) . ' (' . $seq_id . ')') . '</li>';
    }
    echo '</ol>';
    echo '</div>';
}

add_filter('learndash_previous_step_completed', 'fls_bypass_previous_step_for_custom_sequence', 20, 3);
function fls_bypass_previous_step_for_custom_sequence($previous_completed, $post_id, $user_id)
{
    if (empty($post_id) || empty($user_id)) {
        return $previous_completed;
    }

    $course_id = learndash_get_course_id($post_id);
    if (empty($course_id)) {
        return $previous_completed;
    }

    $sequence = fls_get_valid_lesson_sequence($course_id, $user_id);
    if (empty($sequence) || !in_array($post_id, $sequence, true)) {
        return $previous_completed;
    }

    $previous_step = learndash_user_progress_get_previous_incomplete_step($user_id, $course_id, $post_id);
    if (empty($previous_step)) {
        return $previous_completed;
    }

    if (in_array($previous_step, $sequence, true)) {
        return $previous_completed;
    }

    // If the required previous incomplete step does not belong to the learner's
    // selected country track / valid lesson sequence, allow the current lesson.
    return true;
}

add_filter('learndash_course_progression_enabled', 'fls_disable_learndash_progression_for_custom_course', 10, 2);
function fls_disable_learndash_progression_for_custom_course($enabled, $course_id)
{
    $custom_course_ids = array(4137);

    if (in_array(intval($course_id), $custom_course_ids, true)) {
        return true; // Forced enabled for linear progression
    }

    return $enabled;
}

add_filter('learndash_course_next_step_id', 'fls_filter_learndash_next_step_id', 10, 3);

function fls_filter_learndash_next_step_id($next_id, $post, $next_post_obj)
{
    $course_id = learndash_get_course_id($post);

    if (!$course_id) {
        return $next_id;
    }

    if (!in_array($post->post_type, array('sfwd-lessons', 'sfwd-topic', 'sfwd-quiz'))) {
        return $next_id;
    }

    $user_id = get_current_user_id();

    $sequence = fls_get_valid_lesson_sequence($course_id, $user_id);

    if (empty($sequence)) {
        return $next_id;
    }

    $current_index = array_search($post->ID, $sequence, true);

    if ($current_index === false) {
        return $next_id;
    }

    if (isset($sequence[$current_index + 1])) {
        return (int) $sequence[$current_index + 1];
    }

    return 0;
}

/**
 * Filter PREVIOUS Link ID (Dynamic LearnDash Navigation based on Country Track)
 */
add_filter('learndash_course_previous_step_id', 'fls_filter_learndash_previous_step_id', 10, 3);
function fls_filter_learndash_previous_step_id($prev_id, $post, $prev_post_obj)
{
    $course_id = learndash_get_course_id($post);
    if (!$course_id)
        return $prev_id;

    if (!in_array($post->post_type, array('sfwd-lessons', 'sfwd-topic', 'sfwd-quiz'))) {
        return $prev_id;
    }

    $user_id = get_current_user_id();
    $sequence = fls_get_valid_lesson_sequence($course_id, $user_id);

    $current_index = array_search($post->ID, $sequence);
    if ($current_index !== false) {
        // Find prev in our calculated track
        if (isset($sequence[$current_index - 1])) {
            return $sequence[$current_index - 1];
        } else {
            // Reached the beginning, return 0
            return 0;
        }
    }

    return $prev_id;
}


function fls_get_next_lesson_url_after_completion($course_id, $lesson_id, $user_id)
{
    if (empty($course_id) || empty($lesson_id) || empty($user_id)) {
        return get_permalink($lesson_id);
    }

    $sequence = fls_get_valid_lesson_sequence($course_id, $user_id);
    $current_index = array_search((int) $lesson_id, $sequence, true);

    if ($current_index !== false && isset($sequence[$current_index + 1])) {
        return get_permalink($sequence[$current_index + 1]);
    }

    $course_url = get_permalink($course_id);
    return add_query_arg('completed', '1', $course_url);
}

add_filter('learndash_completion_redirect', 'fls_redirect_mark_complete_to_next_lesson', 20, 2);
function fls_redirect_mark_complete_to_next_lesson($redirect_url, $post_id)
{
    if (!is_user_logged_in()) {
        return $redirect_url;
    }

    $post_id = (int) $post_id;
    if ('sfwd-lessons' !== get_post_type($post_id)) {
        return $redirect_url;
    }

    $course_id = learndash_get_course_id($post_id);
    if (!$course_id) {
        return $redirect_url;
    }

    return fls_get_next_lesson_url_after_completion($course_id, $post_id, get_current_user_id());
}


add_filter('learndash_course_step_completion_url', 'fls_redirect_step_completion_to_next_lesson', 20, 3);
function fls_redirect_step_completion_to_next_lesson($redirect_url, $post_id, $course_id)
{
    if (!is_user_logged_in()) {
        return $redirect_url;
    }

    $post_id = (int) $post_id;
    if ('sfwd-lessons' !== get_post_type($post_id)) {
        return $redirect_url;
    }

    $course_id = $course_id ? (int) $course_id : learndash_get_course_id($post_id);
    if (!$course_id) {
        return $redirect_url;
    }

    return fls_get_next_lesson_url_after_completion($course_id, $post_id, get_current_user_id());
}

add_filter('learndash_step_completed_redirect_immediately', 'fls_redirect_lesson_completion_immediately_to_next', 20);
function fls_redirect_lesson_completion_immediately_to_next($redirect_immediately)
{
    if (!is_user_logged_in() || empty($_POST['sfwd_mark_complete']) || empty($_POST['post'])) {
        return $redirect_immediately;
    }

    $post_id = absint(wp_unslash($_POST['post']));
    if ('sfwd-lessons' !== get_post_type($post_id)) {
        return $redirect_immediately;
    }

    return true;
}

/**
 * Shortcode for [customer_email] - Display customer email on thank you page
 */
add_shortcode('customer_email', function () {
    // Check if a trial email was passed in the URL (for school trial users)
    if (!empty($_GET['trial_email'])) {
        return esc_html(sanitize_email(wp_unslash($_GET['trial_email'])));
    }

    // Default WooCommerce order logic (for paid users)
    $order_id = absint(get_query_var('order-received'));
    if (!$order_id && !empty($_GET['order'])) {
        $order_id = absint(wp_unslash($_GET['order']));
    }
    if (!$order_id && !empty($_GET['order-received'])) {
        $order_id = absint(wp_unslash($_GET['order-received']));
    }
    if ($order_id) {
        $order = wc_get_order($order_id);
        if ($order) {
            $student_email = fls_get_order_student_email($order);
            return esc_html($student_email ? $student_email : $order->get_billing_email());
        }
    }
    return '';
});

// =========================================================================
// SCHOOL TRIAL CUSTOM ENROLLMENT FLOW
// =========================================================================

/**
 * 1. AJAX Check if coupon is a school trial coupon
 */
add_action('wp_ajax_check_school_trial_coupon', 'fls_check_school_trial_coupon');
add_action('wp_ajax_nopriv_check_school_trial_coupon', 'fls_check_school_trial_coupon');
function fls_check_school_trial_coupon()
{
    $coupon_code = isset($_POST['coupon_code']) ? strtoupper(trim(sanitize_text_field($_POST['coupon_code']))) : '';
    if (!$coupon_code) {
        wp_send_json_error();
    }

    // Check if LDST_Coupon class exists (meaning ld-school-trial plugin is active)
    if (class_exists('LDST_DB') && class_exists('LDST_Coupon')) {
        $coupon = LDST_Coupon::validate($coupon_code);
        if (!is_wp_error($coupon)) {
            wp_send_json_success(['is_trial' => true]);
        }
    }

    wp_send_json_success(['is_trial' => false]);
}

/**
 * 2. Shortcode for School Trial Enrollment Form [school_trial_enrollment_form]
 */
add_shortcode('school_trial_enrollment_form', 'fls_school_trial_enrollment_form_shortcode');
function fls_school_trial_enrollment_form_shortcode()
{
    $coupon_code = isset($_GET['coupon']) ? sanitize_text_field($_GET['coupon']) : '';
    if (empty($coupon_code)) {
        return '<p>No trial coupon provided.</p>';
    }

    ob_start();
    ?>
        <style>
            .fls-trial-enrollment-form {
                max-width: 500px;
                margin: 0 auto;
                background: #fff;
                padding: 30px;
                border-radius: 8px;
                border: 1px solid #E0E0DB;
                font-family: 'DM Sans', sans-serif !important;
            }

            .fls-trial-enrollment-form h2 {
                margin-top: 0;
                margin-bottom: 20px;
                font-size: 18px;
                color: #1B2A4A;
                border-bottom: 1px solid #E0E0DB;
                padding-bottom: 10px;

            }

            .fls-trial-enrollment-form .form-group {
                margin-bottom: 15px;
            }

            .fls-trial-enrollment-form label {
                display: block;
                margin-bottom: 5px;
                font-weight: 600;
                color: #666;
                font-size: 14px;
            }

            .fls-trial-enrollment-form input[type="text"],
            .fls-trial-enrollment-form input[type="email"],
            .fls-trial-enrollment-form input[type="tel"],
            .fls-trial-enrollment-form select {
                width: 100%;
                border: 1px solid #D4D4CF;
                border-radius: 6px;
                font-size: 16px;
                background-color: #f5f5f0 !important;
                font-family: 'DM Sans', sans-serif !important;
            }

            .fls-trial-enrollment-form .btn-submit {
                background-color: #85BB65;
                color: #fff;
                border: none;
                padding: 15px 20px;
                width: 100%;
                font-size: 18px;
                font-weight: bold;
                border-radius: 6px;
                cursor: pointer;
                margin-top: 10px;
                font-family: 'DM Sans', sans-serif !important;
            }

            .fls-trial-enrollment-form .btn-submit:hover {
                background-color: #2F8A55;
            }

            .fls-trial-error {
                color: #E53E3E;
                margin-bottom: 15px;
                display: none;
            }

            .fls-trial-loading {
                display: none;
                text-align: center;
                margin-top: 15px;
                color: #718096;
            }
        </style>

        <div class="fls-trial-enrollment-form">
            <h2>School Trial Enrollment</h2>
            <div class="fls-trial-error" id="trial-error-msg"></div>
            <form id="fls-school-trial-form">
                <?php wp_nonce_field('fls_trial_enrollment_nonce', 'security'); ?>
                <input type="hidden" name="action" value="process_school_trial_enrollment">
                <input type="hidden" name="coupon_code" value="<?php echo esc_attr($coupon_code); ?>">

                <div class="form-group">
                    <label for="trial_role">I am a:</label>
                    <select id="trial_role" name="role" required>
                        <option value="">Select your role...</option>
                        <option value="student">Student</option>
                        <option value="teacher">School Staff</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="trial_first_name">First Name</label>
                    <input type="text" id="trial_first_name" name="first_name" required>
                </div>

                <div class="form-group">
                    <label for="trial_last_name">Last Name</label>
                    <input type="text" id="trial_last_name" name="last_name" required>
                </div>

                <div class="form-group">
                    <label for="trial_email">Email Address</label>
                    <input type="email" id="trial_email" name="email" required>
                </div>

                <button type="submit" class="btn-submit" id="trial-submit-btn">Enrol Now</button>
                <div class="fls-trial-loading" id="trial-loading-msg">Processing enrollment, please wait...</div>
            </form>
        </div>

        <script>
            jQuery(document).ready(function ($) {
                $('#fls-school-trial-form').on('submit', function (e) {
                    e.preventDefault();

                    var $form = $(this);
                    var $btn = $('#trial-submit-btn');
                    var $error = $('#trial-error-msg');
                    var $loading = $('#trial-loading-msg');

                    $error.hide();
                    $btn.prop('disabled', true);
                    $loading.show();

                    $.ajax({
                        type: 'POST',
                        url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
                        data: $form.serialize(),
                        success: function (response) {
                            if (response.success) {
                                window.location.href = response.data.redirect_url;
                            } else {
                                $error.text(response.data.message).show();
                                $btn.prop('disabled', false);
                                $loading.hide();
                            }
                        },
                        error: function () {
                            $error.text('An error occurred. Please try again later.').show();
                            $btn.prop('disabled', false);
                            $loading.hide();
                        }
                    });
                });
            });
        </script>
        <?php
        return ob_get_clean();
}

/**
 * 3. Process the School Trial Enrollment Form
 */
add_action('wp_ajax_process_school_trial_enrollment', 'fls_process_school_trial_enrollment');
add_action('wp_ajax_nopriv_process_school_trial_enrollment', 'fls_process_school_trial_enrollment');
function fls_process_school_trial_enrollment()
{
    check_ajax_referer('fls_trial_enrollment_nonce', 'security');

    $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';
    $role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '';
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

    if (empty($coupon_code) || empty($role) || empty($first_name) || empty($email)) {
        wp_send_json_error(['message' => 'Please fill all required fields.']);
    }

    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Invalid email address.']);
    }

    // 1. Verify coupon
    if (!class_exists('LDST_DB') || !class_exists('LDST_Coupon') || !class_exists('LDST_Checkout')) {
        wp_send_json_error(['message' => 'Trial system is currently unavailable.']);
    }

    $coupon = LDST_DB::get_coupon($coupon_code);
    if (!$coupon || $coupon->status !== 'active') {
        wp_send_json_error(['message' => 'Invalid or expired trial coupon.']);
    }

    $validation = LDST_Coupon::validate($coupon_code);
    if (is_wp_error($validation)) {
        wp_send_json_error(['message' => $validation->get_error_message()]);
    }

    // 2. Find or Create User
    $user = get_user_by('email', $email);

    /**
     * -------------------------------------------------
     * PREVENT DUPLICATE FREE TRIALS
     * -------------------------------------------------
     */

    if ($user) {

        $user_id = $user->ID;

        /**
         * Block if already has active course access
         */
        if (sfwd_lms_has_access(4137, $user_id)) {

            wp_send_json_error([
                'message' => 'You already have active access to this programme. Please log into your account.'
            ]);
        }

        /**
         * Block if free trial already used
         */
        $trial_used = get_user_meta($user_id, 'fls_trial_used', true);

        if ($trial_used === 'yes') {

            wp_send_json_error([
                'message' => 'This email has already used the free school trial.'
            ]);
        }
    }

    if (!$user) {
        $username = sanitize_title($first_name . '-' . $last_name);
        $username = str_replace('-', '', $username);
        if (username_exists($username) || empty($username)) {
            $username .= wp_rand(100, 999);
        }

        $password = wp_generate_password();

        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => 'Error creating account: ' . $user_id->get_error_message()]);
        }

        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
        ]);

        $user = get_userdata($user_id);
    } else {
        $user_id = $user->ID;
        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);
    }

    update_user_meta($user_id, 'fls_school_trial_role', $role);

    // 3. Prevent duplicate trial use for the same course
    $trials = LDST_DB::get_user_trials($user_id);
    $already_has_trial = false;
    foreach ($trials as $t) {
        if ((int) $t['course_id'] === (int) $coupon->course_id) {
            $already_has_trial = true;
            break;
        }
    }

    if ($already_has_trial) {

        wp_send_json_error([
            'message' => 'This email has already used the free school trial.'
        ]);

    } else {
        // If they already have permanent access (paid), don't allow trial
        if (function_exists('sfwd_lms_has_access') && sfwd_lms_has_access($coupon->course_id, $user_id)) {
            wp_send_json_error(['message' => 'You already have access to this programme.']);
        }

        if ($coupon->group_id > 0 && function_exists('ld_update_group_access')) {
            ld_update_group_access($user_id, (int) $coupon->group_id);
        } else {
            if (function_exists('ld_update_course_access')) {
                ld_update_course_access($user_id, (int) $coupon->course_id);
            }
        }
        LDST_Checkout::activate_trial($user_id, $coupon);
        /**
         * Mark trial as permanently used
         */
        update_user_meta($user_id, 'fls_trial_used', 'yes');
    }

    // 4. Generate Magic Login link and send emails
    $redirect_to = home_url('/courses/financial-life-skills');
    $magic_link = '';

    if (function_exists('fls_get_magic_login_link')) {
        $magic_link = fls_get_magic_login_link($user, $redirect_to);
    }

    // Get exact dates from coupon
    $start_date = date('F j, Y'); // Or date('F j, Y', strtotime($coupon->trial_start_date)) depending on preference, but user said "user jab se coupon use kare uss current date se end date tak".
    // "user jab se coupon use kare uss current date se end date tak ka hi usse trail bataye phir chaahe user ke paas 2 din bache ya 7 din ka"
    $start_date = date('d M Y'); // Today
    $end_date = date('d M Y', strtotime($coupon->trial_end_date));

    // Prepare Email
    $subject = 'Welcome to Financial Life Skills';
    if ($role === 'teacher') {
        $subject = 'Welcome to Your Financial Life Skills Preview';
    }

    if ($magic_link) {
        $body = fls_get_trial_magic_link_welcome_email_body($user, $email, $magic_link, $role, $start_date, $end_date);
    } else {
        $body = "Hi $first_name,\n\n";
        if ($role === 'student') {
            $body .= "Welcome to your Financial Life Skills student trial.\n";
        } else {
            $body .= "Welcome to your Financial Life Skills teacher trial.\n";
        }
        $body .= "Your free trial period is from $start_date to $end_date.\n\n";
        $body .= "You can login at: " . wp_login_url() . "\n\n";
        $body .= "Enjoy the course!";
        $body = nl2br($body);
    }

    wp_mail($email, $subject, $body, ['Content-Type: text/html; charset=UTF-8']);

    // Admin Notification
    $admin_email = get_option('admin_email');
    $admin_subject = "New School Trial Enrollment ($role)";
    $admin_body = "A new user has enrolled via school trial.\n\n"
        . "Name: $first_name $last_name\n"
        . "Email: $email\n"
        . "Role: $role\n"
        . "Coupon: $coupon_code\n";
    wp_mail($admin_email, $admin_subject, $admin_body);

    // 5. Success and redirect
    // Redirect to the custom Thank You page with the email parameter so the shortcode works
    $thank_you_url = home_url('/thank-you-page/?trial_email=' . urlencode($email));

    wp_send_json_success(['redirect_url' => $thank_you_url]);
}


function custom_magiclink_script()
{
    ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {

                function handleMagicLinkUI() {
                    const messageBox = document.querySelector("#magic-login-register .registration_result .info");
                    const form = document.querySelector("#magic_login_registration_form");

                    if (messageBox && !document.querySelector(".enroll-now-btn")) {

                        // ✅ Change message
                        messageBox.innerHTML = "It looks like you haven't enrolled yet. Let's get you started";

                        // ✅ Create button
                        const btn = document.createElement("a");
                        btn.href = "https://financiallifeskills.com/checkout/?add-to-cart=3848"; // apna checkout URL
                        btn.innerText = "Enrol Your Teen Now";
                        btn.className = "enroll-now-btn";

                        // Add button
                        messageBox.parentElement.appendChild(btn);

                        // ✅ Hide form
                        if (form) {
                            form.style.display = "none";
                        }
                    }
                }

                // 🔁 AJAX content detect karne ke liye MutationObserver
                const observer = new MutationObserver(function () {
                    handleMagicLinkUI();
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });

                // Initial run
                handleMagicLinkUI();

            });
        </script>

        <style>
            .enroll-now-btn {
                display: inline-block;
                margin-top: 15px;
                padding: 12px 30px;
                background-color: #85BB65;
                color: #292A61 !important;
                text-decoration: none;
                border-radius: 50px;
                font-weight: 500;
                text-decoration: none !important;
                border: 1px solid #85BB65;
                width: 100%;
                text-align: center;
            }

            .enroll-now-btn:hover {
                background-color: transparent;
                color: #85BB65 !important;
            }

            .info {
                text-align: center;
            }
        </style>
        <?php
}
add_action('wp_footer', 'custom_magiclink_script');

/**
 * Enforce sequential lesson access for LearnDash lessons
 */
add_filter('learndash_content', 'fls_enforce_lesson_progression', 10, 2);
function fls_enforce_lesson_progression($content, $post)
{
    if (is_singular('sfwd-lessons') && $post->post_type === 'sfwd-lessons') {
        // ✅ Ye lessons exclude karo — inpe filter apply mat karo
        $excluded_lesson_ids = array(6898, 6904, 7149, 7155, 7161, 7167, 7173, 7230, 7236);
        if (in_array($post->ID, $excluded_lesson_ids, true)) {
            return $content;
        }
        $lesson_id = $post->ID;
        $course_id = learndash_get_course_id($lesson_id);
        $user_id = get_current_user_id();

        // Check if user has access to the course
        if (!sfwd_lms_has_access($course_id, $user_id)) {
            // Not enrolled, show enrollment message or redirect
            return '<div style="text-align: center; padding: 50px;"><h2>Please enroll in the course to access this lesson.</h2></div>';
        }

        // Check if lesson is accessible (includes progression)
        if (!sfwd_lms_has_access($lesson_id, $user_id)) {
            // Lesson not accessible, likely due to progression
            return '<div style="text-align: center; padding: 50px;"><h2>This lesson isn\'t available yet. Please complete the lessons in order. Each one builds on the last, so going step by step will help you get the most out of the program.</h2></div>';
        }
    }

    return $content;
}

/**
 * Locked Lesson pe Custom Message Show karo
 * Jab user seedha locked lesson pe jaaye to blank ki jagah message dikhe
 */
add_action('template_redirect', 'fls_redirect_locked_lesson');
function fls_redirect_locked_lesson()
{
    if (!is_singular('sfwd-lessons'))
        return;
    if (isset($_GET['locked']))
        return; // Already redirect ho chuka hai

    $lesson_id = get_the_ID();
    $user_id = get_current_user_id();

    if (!$user_id)
        return; // Guest handle mat karo yahan

    $course_id = learndash_get_course_id($lesson_id);
    if (!$course_id)
        return;

    // Course access nahi hai to kuch mat karo
    if (!sfwd_lms_has_access($course_id, $user_id))
        return;

    // Lesson already complete hai to allow karo
    if (learndash_is_lesson_complete($user_id, $lesson_id, $course_id))
        return;

    // Aapka custom sequence lo
    $sequence = fls_get_valid_lesson_sequence($course_id, $user_id);

    if (empty($sequence))
        return;

    // Current lesson sequence mein hai ya nahi
    $current_index = array_search($lesson_id, $sequence);
    if ($current_index === false)
        return; // Sequence mein nahi hai to kuch mat karo

    // Pehla lesson hai to hamesha allow karo
    if ($current_index === 0)
        return;

    // Previous lesson check karo
    $prev_lesson_id = $sequence[$current_index - 1];

    // Previous lesson complete hai to allow karo
    if (learndash_is_lesson_complete($user_id, $prev_lesson_id, $course_id))
        return;

    // Yahan tak aaya matlab lesson locked hai
    $prev_title = get_the_title($prev_lesson_id);
    $prev_url = get_permalink($prev_lesson_id);

    $redirect_url = add_query_arg(array(
        'locked' => '1',
        'prev_lesson' => urlencode($prev_title),
        'prev_url' => urlencode($prev_url),
        'locked_title' => urlencode(get_the_title($lesson_id)),
    ), get_permalink($lesson_id));

    wp_safe_redirect($redirect_url);
    exit;
}

/**
 * Locked Lesson Message — Elementor page ke upar inject karo
 */
add_action('wp_head', 'fls_locked_lesson_message_styles');
function fls_locked_lesson_message_styles()
{
    if (!is_singular('sfwd-lessons'))
        return;
    if (!isset($_GET['locked']))
        return;
    ?>
        <style>
            /* Elementor content hide karo */
            .elementor-section-wrap,
            .elementor-inner,
            .entry-content,
            .learndash-wrapper .ld-lesson-content {
                display: none !important;
            }

            /* Locked message overlay */
            #fls-locked-overlay {
                position: fixed;
                inset: 0;
                z-index: 99999;
                background: #f7f9fc;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                font-family: 'DM Sans', sans-serif;
            }

            .fls-locked-card {
                background: #ffffff;
                border-radius: 16px;
                padding: 48px 40px;
                max-width: 520px;
                width: 100%;
                text-align: center;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
                border: 1px solid #E0E0DB;
            }

            .fls-locked-icon {
                width: 72px;
                height: 72px;
                background: #FEF3C7;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 24px;
            }

            .fls-locked-icon svg {
                width: 32px;
                height: 32px;
                stroke: #D97706;
            }

            .fls-locked-card h2 {
                font-size: 22px;
                font-weight: 700;
                color: #1B2A4A;
                margin: 0 0 12px;
            }

            .fls-locked-card .fls-locked-subtitle {
                font-size: 15px;
                color: #292a61;
                margin: 0 0 15px;
                line-height: 1.6;
            }

            .fls-locked-card .fls-locked-prev-name {
                display: inline-block;
                background: #F0FDF4;
                color: #166534;
                border: 1px solid #BBF7D0;
                border-radius: 8px;
                padding: 10px 18px;
                font-size: 14px;
                font-weight: 600;
                margin: 12px 0 28px;
            }

            .fls-locked-card .fls-progress-info {
                background: #EFF6FF;
                border: 1px solid #BFDBFE;
                border-radius: 10px;
                padding: 14px 18px;
                font-size: 13px;
                color: #1E40AF;
                margin-bottom: 28px;
                line-height: 1.5;
            }

            .fls-locked-btn-primary {
                display: inline-block;
                background: #85bb65;
                color: #ffffff !important;
                text-decoration: none !important;
                padding: 14px 32px;
                border-radius: 10px;
                font-size: 15px;
                font-weight: 700;
                margin-bottom: 14px;
                width: 100%;
                box-sizing: border-box;
                transition: background 0.2s;
            }

            .fls-locked-btn-primary:hover {
                background: #85bb65;
                color: #fff !important;
            }

            .fls-locked-btn-secondary {
                display: inline-block;
                background: transparent;
                color: #6B7280 !important;
                text-decoration: none !important;
                padding: 12px 24px;
                border-radius: 10px;
                font-size: 14px;
                border: 1px solid #E0E0DB;
                width: 100%;
                box-sizing: border-box;
                transition: all 0.2s;
            }

            .fls-locked-btn-secondary:hover {
                border-color: #3EAA6D;
                color: #3EAA6D !important;
            }
        </style>
        <?php
}

/**
 * Locked Lesson HTML — body ke andar inject karo
 */
add_action('wp_footer', 'fls_locked_lesson_message_html');
function fls_locked_lesson_message_html()
{
    if (!is_singular('sfwd-lessons'))
        return;
    if (!isset($_GET['locked']))
        return;

    $prev_lesson_title = isset($_GET['prev_lesson']) ? sanitize_text_field(urldecode($_GET['prev_lesson'])) : 'Previous Lesson';
    $prev_lesson_url = isset($_GET['prev_url']) ? esc_url(urldecode($_GET['prev_url'])) : '#';
    $locked_lesson_title = isset($_GET['locked_title']) ? sanitize_text_field(urldecode($_GET['locked_title'])) : 'This Lesson';

    $course_url = '';
    $lesson_id = get_the_ID();
    $course_id = learndash_get_course_id($lesson_id);
    if ($course_id) {
        $course_url = get_permalink($course_id);
    }
    ?>
        <div id="fls-locked-overlay" role="alert" aria-live="polite">
            <div class="fls-locked-card">

                <!-- Lock Icon -->
                <div class="fls-locked-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </div>

                <h2>This Lesson is Locked</h2>

                <p class="fls-locked-subtitle">
                    This lesson isn't available yet. Please complete the lessons in order. Each one builds on the last, so going
                    step by step will help you get the most out of the program.<br>
                </p>

                <!-- CTA Buttons -->
                <a href="<?php echo esc_url($prev_lesson_url); ?>" class="fls-locked-btn-primary">
                    Go to Previous Lesson →
                </a>

                <?php if ($course_url): ?>
                        <a href="<?php echo esc_url($course_url); ?>" class="fls-locked-btn-secondary">
                            Back to Course Overview
                        </a>
                <?php endif; ?>

            </div>
        </div>
        <?php
}

/**
 * -------------------------------------------------
 * REMOVE DEFAULT TERMS CHECKBOX
 * -------------------------------------------------
 */
add_filter('woocommerce_checkout_show_terms', '__return_false');


/**
 * -------------------------------------------------
 * ADD CUSTOM CONSENT CHECKBOXES
 * -------------------------------------------------
 */
add_action('woocommerce_review_order_before_submit', 'custom_checkout_consents');

function custom_checkout_consents()
{

    // Terms Checkbox (Required)
    woocommerce_form_field('custom_terms_consent', array(
        'type' => 'checkbox',
        'class' => array('form-row-wide'),
        'label' => 'I agree to the <a href="https://bagheeraventures.com/financiallifeskills/term-conditions/" target="_blank" rel="noopener noreferrer">Terms and Conditions</a>',
        'required' => true,
    ));


    // WhatsApp Checkbox (Optional)
    woocommerce_form_field('custom_whatsapp_consent', array(
        'type' => 'checkbox',
        'class' => array('form-row-wide'),
        'label' => 'I agree to be added to the destination-specific WhatsApp group for parents. I can leave anytime.',
        'required' => false,
    ));

}


/**
 * -------------------------------------------------
 * VALIDATE REQUIRED TERMS CHECKBOX
 * -------------------------------------------------
 */
add_action('woocommerce_checkout_process', 'validate_custom_terms_consent');

function validate_custom_terms_consent()
{

    if (!isset($_POST['custom_terms_consent'])) {

        wc_add_notice(
            'Please accept the Terms and Conditions.',
            'error'
        );

    }

}


/**
 * -------------------------------------------------
 * SAVE CONSENT DATA
 * -------------------------------------------------
 */
add_action('woocommerce_checkout_create_order', 'save_custom_consent_data', 20, 2);

function save_custom_consent_data($order, $data)
{

    // Timestamp
    $timestamp = current_time('mysql');

    // IP Address
    $ip_address = WC_Geolocation::get_ip_address();

    // User Agent
    $user_agent = $_SERVER['HTTP_USER_AGENT'];


    /**
     * TERMS CONSENT
     */
    if (isset($_POST['custom_terms_consent'])) {

        $order->update_meta_data(
            '_terms_consent_status',
            'Accepted'
        );

        $order->update_meta_data(
            '_terms_consent_text',
            'I agree to the Terms and Conditions'
        );

        $order->update_meta_data(
            '_terms_consent_timestamp',
            $timestamp
        );

    }


    /**
     * WHATSAPP CONSENT
     */
    if (isset($_POST['custom_whatsapp_consent'])) {

        $order->update_meta_data(
            '_whatsapp_consent_status',
            'Accepted'
        );

        $order->update_meta_data(
            '_whatsapp_consent_text',
            'I agree to be added to the destination-specific WhatsApp group for parents. I can leave anytime.'
        );

        $order->update_meta_data(
            '_whatsapp_consent_timestamp',
            $timestamp
        );

    }


    /**
     * COMMON DATA
     */
    $order->update_meta_data(
        '_consent_ip_address',
        $ip_address
    );

    $order->update_meta_data(
        '_consent_user_agent',
        $user_agent
    );

}


/**
 * -------------------------------------------------
 * SHOW CONSENT DATA IN ADMIN ORDER PAGE
 * -------------------------------------------------
 */
add_action(
    'woocommerce_admin_order_data_after_billing_address',
    'display_custom_consent_data_admin'
);

function display_custom_consent_data_admin($order)
{

    echo '<div style="margin-top:20px;padding:15px;border:1px solid #ddd;background:#fff;">';

    echo '<h3>Consent Details</h3>';


    // Terms Consent
    echo '<p><strong>Terms Consent:</strong> ' .
        esc_html($order->get_meta('_terms_consent_status')) .
        '</p>';

    echo '<p><strong>Terms Accepted At:</strong> ' .
        esc_html($order->get_meta('_terms_consent_timestamp')) .
        '</p>';


    // WhatsApp Consent
    echo '<p><strong>WhatsApp Consent:</strong> ' .
        esc_html($order->get_meta('_whatsapp_consent_status')) .
        '</p>';

    echo '<p><strong>WhatsApp Accepted At:</strong> ' .
        esc_html($order->get_meta('_whatsapp_consent_timestamp')) .
        '</p>';


    // IP Address
    echo '<p><strong>IP Address:</strong> ' .
        esc_html($order->get_meta('_consent_ip_address')) .
        '</p>';

    echo '</div>';

}


/**
 * -------------------------------------------------
 * CHANGE PLACE ORDER BUTTON TEXT
 * -------------------------------------------------
 */
add_filter('woocommerce_order_button_text', 'custom_order_button_text');

function custom_order_button_text()
{

    return 'Enrol Your Teen Now';

}
/**
 * -------------------------------------------------
 * CHANGE Country in checkout page
 * -------------------------------------------------
 */
add_filter('default_checkout_billing_country', 'custom_default_checkout_country');
add_filter('default_checkout_shipping_country', 'custom_default_checkout_country');

function custom_default_checkout_country()
{
    $location = WC_Geolocation::geolocate_ip();

    return !empty($location['country']) ? $location['country'] : 'IN';
}

/**
 * -------------------------------------------------
 * CHANGE coupon text in checkout page
 * -------------------------------------------------
 */
add_filter('woocommerce_checkout_coupon_message', 'custom_coupon_message');

function custom_coupon_message()
{

    return 'Partner code? <a href="#" class="showcoupon">Click here to enter your code</a>';

}

/**
 * -------------------------------------------------
 * CHANGE coupon field texts
 * -------------------------------------------------
 */
add_filter('gettext', 'custom_coupon_field_texts', 20, 3);

function custom_coupon_field_texts($translated, $text, $domain)
{
    if ($domain === 'woocommerce') {

        // Placeholder
        if ($text === 'Coupon code') {
            $translated = 'Partner Code';
        }

        // Button Text
        if ($text === 'Apply coupon') {
            $translated = 'Apply Code';
        }

        // Success Message
        if ($text === 'Coupon code applied successfully.') {
            $translated = 'Partner code applied successfully.';
        }

        // Already Applied Message
        if ($text === 'Coupon code "%s" already applied!') {
            $translated = 'Partner code "%s" already applied!';
        }

        // Removed Message
        if ($text === 'Coupon has been removed.') {
            $translated = 'Partner code has been removed.';
        }
    }

    return $translated;
}

/**
 * -------------------------------------------------
 * CREATE STUDENT ACCOUNT + LEARNDASH ENROLLMENT
 * -------------------------------------------------
 */

add_action('woocommerce_order_status_processing', 'custom_create_student_account_and_enroll');
add_action('woocommerce_order_status_completed', 'custom_create_student_account_and_enroll');

function custom_create_student_account_and_enroll($order_id)
{

    /**
     * Prevent duplicate processing
     */
    if (get_post_meta($order_id, '_student_account_created', true)) {
        return;
    }

    /**
     * Get order
     */
    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    /**
     * -------------------------------------------------
     * STUDENT DATA
     * -------------------------------------------------
     */
    $student_email = sanitize_email(
        $order->get_meta('billing_student_email')
    );

    $student_first_name = sanitize_text_field(
        $order->get_meta('billing_student_first_name')
    );

    $student_last_name = sanitize_text_field(
        $order->get_meta('billing_student_last_name')
    );

    $student_school_name = sanitize_text_field(
        $order->get_meta('billing_school_name')
    );

    /**
     * -------------------------------------------------
     * PARENT DATA
     * -------------------------------------------------
     */
    $parent_email = sanitize_email(
        $order->get_billing_email()
    );

    /**
     * Stop if no student email
     */
    if (empty($student_email)) {
        return;
    }

    /**
     * -------------------------------------------------
     * CHECK IF USER EXISTS
     * -------------------------------------------------
     */
    $existing_user = get_user_by('email', $student_email);

    if ($existing_user) {

        $student_user_id = $existing_user->ID;

    } else {

        /**
         * -------------------------------------------------
         * CREATE USERNAME
         * -------------------------------------------------
         */
        $base_username = sanitize_user(
            current(explode('@', $student_email))
        );

        $username = $base_username;
        $counter = 1;

        while (username_exists($username)) {

            $username = $base_username . $counter;
            $counter++;

        }

        /**
         * -------------------------------------------------
         * GENERATE PASSWORD
         * -------------------------------------------------
         */
        $password = wp_generate_password(12, true, true);

        /**
         * -------------------------------------------------
         * CREATE USER
         * -------------------------------------------------
         */
        $student_user_id = wp_create_user(
            $username,
            $password,
            $student_email
        );

        /**
         * Stop if failed
         */
        if (is_wp_error($student_user_id)) {
            return;
        }

        /**
         * -------------------------------------------------
         * UPDATE USER DATA
         * -------------------------------------------------
         */
        wp_update_user(array(
            'ID' => $student_user_id,
            'first_name' => $student_first_name,
            'last_name' => $student_last_name,
            'display_name' => trim(
                $student_first_name . ' ' . $student_last_name
            ),
            'role' => 'customer',
        ));

        /**
         * -------------------------------------------------
         * SAVE USER META
         * -------------------------------------------------
         */

        // Parent Email
        update_user_meta(
            $student_user_id,
            'parent_email',
            $parent_email
        );

        // School Name
        update_user_meta(
            $student_user_id,
            'school_name',
            $student_school_name
        );

        // Student First Name
        update_user_meta(
            $student_user_id,
            'student_first_name',
            $student_first_name
        );

        // Student Last Name
        update_user_meta(
            $student_user_id,
            'student_last_name',
            $student_last_name
        );

    }

    /**
     * -------------------------------------------------
     * LINK ORDER TO STUDENT USER
     * -------------------------------------------------
     */
    update_post_meta(
        $order_id,
        '_student_user_id',
        $student_user_id
    );

    /**
     * -------------------------------------------------
     * LEARNDASH ENROLLMENT
     * -------------------------------------------------
     */
    if (function_exists('ld_update_course_access')) {

        foreach ($order->get_items() as $item) {

            $product_id = $item->get_product_id();

            /**
             * Get linked LearnDash courses
             */

            /*
            |--------------------------------------------------------------------------
            | DIRECT LEARNDASH ENROLLMENT
            |--------------------------------------------------------------------------
            */

            $course_id = 4137; // YOUR LEARNDASH COURSE ID

            if (function_exists('ld_update_course_access')) {

                ld_update_course_access(
                    $student_user_id,
                    $course_id,
                    false
                );

                error_log(
                    'FLS ENROLLED USER '
                    . $student_user_id
                    . ' INTO COURSE '
                    . $course_id
                );

                // Save in order meta
                update_post_meta(
                    $order_id,
                    '_ld_course_enrolled',
                    'yes'
                );

            }

        }

    }

    /**
     * -------------------------------------------------
     * MARK AS PROCESSED
     * -------------------------------------------------
     */
    update_post_meta(
        $order_id,
        '_student_account_created',
        'yes'
    );

}

/**
 * Completely disable WooCommerce checkout registration
 */
add_filter('woocommerce_checkout_registration_enabled', '__return_false');

add_filter('woocommerce_checkout_registration_required', '__return_false');

/**
 * Remove account fields from checkout
 */
add_filter('woocommerce_checkout_fields', 'custom_remove_checkout_account_fields');

function custom_remove_checkout_account_fields($fields)
{

    if (isset($fields['account'])) {
        unset($fields['account']);
    }

    return $fields;

}

/**
 * Completely disable WooCommerce customer account creation
 */
add_filter('woocommerce_checkout_registration_enabled', '__return_false', 9999);

add_filter('woocommerce_checkout_registration_required', '__return_false', 9999);

/**
 * Force checkout as guest only
 */
add_filter('woocommerce_checkout_customer_id', function () {
    return 0;
}, 9999);

/**
 * Stop WooCommerce from auto creating customer
 */
add_action('woocommerce_checkout_process', function () {

    if (isset($_POST['createaccount'])) {
        unset($_POST['createaccount']);
    }

}, 1);

/**
 * Remove customer creation completely
 */
add_filter('woocommerce_checkout_posted_data', function ($data) {

    $data['createaccount'] = 0;

    return $data;

}, 9999);


/**
 * Load International Phone Input
 */
add_action('wp_enqueue_scripts', 'custom_load_intl_phone_input');

function custom_load_intl_phone_input()
{

    if (is_checkout()) {

        wp_enqueue_style(
            'intl-tel-input-css',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css',
            array(),
            '18.2.1'
        );

        wp_enqueue_script(
            'intl-tel-input-js',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js',
            array('jquery'),
            '18.2.1',
            true
        );

        wp_enqueue_script(
            'intl-tel-utils',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js',
            array('intl-tel-input-js'),
            '18.2.1',
            true
        );

    }

}

/**
 * Initialize International Phone Field
 */
add_action('wp_footer', 'custom_init_phone_field');

function custom_init_phone_field()
{

    if (!is_checkout()) {
        return;
    }

    ?>

        <script>
            jQuery(document).ready(function ($) {

                var input = document.querySelector("#billing_phone");

                if (input) {

                    var iti = window.intlTelInput(input, {

                        initialCountry: "auto",

                        geoIpLookup: function (callback) {

                            fetch('https://ipapi.co/json/')
                                .then(function (res) {
                                    return res.json();
                                })
                                .then(function (data) {
                                    callback(data.country_code);
                                })
                                .catch(function () {
                                    callback('sg');
                                });

                        },

                        preferredCountries: ["sg", "in", "us", "gb"],

                        separateDialCode: true,

                        nationalMode: false,

                        autoPlaceholder: "polite",

                        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"

                    });

                    /**
                     * Save full number with country code
                     */
                    $('form.checkout').on('checkout_place_order', function () {

                        input.value = iti.getNumber();

                    });

                }

            });
        </script>

        <?php

}


/*
|--------------------------------------------------------------------------
| LearnDash Notification Magic Login Shortcode
|--------------------------------------------------------------------------
| Usage:
| [fls_magic_login]
|--------------------------------------------------------------------------
*/

add_shortcode('fls_magic_login', 'fls_magic_login_shortcode');

function fls_magic_login_shortcode()
{

    if (!is_user_logged_in()) {
        return '';
    }

    $user = wp_get_current_user();

    if (!$user || empty($user->user_email)) {
        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | Redirect URL
    |--------------------------------------------------------------------------
    */

    $redirect_to = home_url('/courses/financial-life-skills');

    /*
    |--------------------------------------------------------------------------
    | Generate Magic Link
    |--------------------------------------------------------------------------
    */

    if (!function_exists('fls_get_magic_login_link')) {
        return '';
    }

    $magic_link = fls_get_magic_login_link(
        $user,
        $redirect_to
    );

    if (empty($magic_link)) {
        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | Return Button HTML
    |--------------------------------------------------------------------------
    */

    return '
    <div style="text-align:center;margin:40px 0;">
        <a href="' . esc_url($magic_link) . '" 
        style="
            display:inline-block;
            background:#0f766e;
            color:#ffffff;
            padding:18px 36px;
            border-radius:10px;
            text-decoration:none;
            font-size:16px;
            font-weight:700;
        ">
            Login To Your Program
        </a>
    </div>';
}

/**
 * --------------------------------------------------------
 * FOUNDATION COMPLETION EMAIL
 * --------------------------------------------------------
 */

add_action(
    'learndash_lesson_completed',
    'fls_send_foundation_completion_email',
    20,
    1
);

function fls_send_foundation_completion_email($data)
{

    if (empty($data['user']) || empty($data['lesson'])) {
        return;
    }

    $user = $data['user'];
    $lesson = $data['lesson'];

    $user_id = $user->ID;
    /*
|--------------------------------------------------------------------------
| Skip Trial Users
|--------------------------------------------------------------------------
*/

    $is_trial_user = get_user_meta(
        $user_id,
        'fls_school_trial_role',
        true
    );

    if (!empty($is_trial_user)) {
        return;
    }
    $lesson_id = $lesson->ID;

    /*
    |--------------------------------------------------------------------------
    | Foundation Completion Lesson ID
    |--------------------------------------------------------------------------
    */
    $course_id = learndash_get_course_id($lesson_id);
    $foundation_completion_lesson_id = fls_get_foundation_completion_lesson_id($course_id, $user_id);
    if ((int) $lesson_id !== (int) $foundation_completion_lesson_id) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Email
    |--------------------------------------------------------------------------
    */

    $already_sent = get_user_meta(
        $user_id,
        '_fls_foundation_email_sent',
        true
    );

    if ($already_sent) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Build Magic Link
    |--------------------------------------------------------------------------
    */

    $redirect_to = home_url('/courses/financial-life-skills');

    $magic_link = '';

    if (function_exists('fls_get_magic_login_link')) {

        $magic_link = fls_get_magic_login_link(
            $user,
            $redirect_to
        );

    }

    $display_name = $user->first_name
        ? $user->first_name
        : $user->display_name;

    /*
    |--------------------------------------------------------------------------
    | Email Content
    |--------------------------------------------------------------------------
    */

    $subject = "You've completed the Foundation Module";

    $message = '<div style="font-family:Arial,Helvetica,sans-serif;color:#1f2937;line-height:1.7;max-width:650px;margin:0 auto;">';

    $message .= '<p style="font-size:22px;font-weight:700;margin-bottom:20px;">';
    $message .= 'Hi ' . esc_html($display_name) . ',';
    $message .= '</p>';

    $message .= '<p style="font-size:16px;margin-bottom:20px;">';
    $message .= 'Nice work! You\'ve finished the Foundation Module, the first part of the Financial Life Skills program.';
    $message .= '</p>';

    $message .= '<p style="font-size:16px;margin-bottom:25px;">';
    $message .= 'Once your university offer is confirmed, log back in and select your country.';
    $message .= ' That\'s where you will find the destination-specific essentials: budgeting, banking, and tax for where you are heading.';
    $message .= ' Just remember to complete it within your 1-year access period.';
    $message .= '</p>';

    if (!empty($magic_link)) {

        $message .= '
        <div style="text-align:center;margin:40px 0;">
            <a href="' . esc_url($magic_link) . '"
            style="
                display:inline-block;
                background:#0f766e;
                color:#ffffff;
                padding:18px 36px;
                border-radius:10px;
                text-decoration:none;
                font-size:16px;
                font-weight:700;
            ">
                Login To Your Program
            </a>
        </div>';

    }

    $message .= '<p style="font-size:16px;margin-top:35px;">';
    $message .= 'See you back soon,<br>';
    $message .= '<strong>The Financial Life Skills Team</strong>';
    $message .= '</p>';

    $message .= '</div>';

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: Financial Life Skills <hello@financiallifeskills.com>'
    );

    /*
    |--------------------------------------------------------------------------
    | Send Email
    |--------------------------------------------------------------------------
    */

    $message = fls_get_premium_email_template($message);

    // Removed as per client request (PDF)
    // wp_mail(
    //     $user->user_email,
    //     $subject,
    //     $message,
    //     $headers
    // );


    /*
    |--------------------------------------------------------------------------
    | Mark Sent
    |--------------------------------------------------------------------------
    */

    update_user_meta(
        $user_id,
        '_fls_foundation_email_sent',
        1
    );

}

/**
 * Force unlock Mark Complete button for City-Based Budget Simulator.
 * This bypasses the LearnDash timer pausing issue when interacting with the iframe.
 */
add_action('wp_footer', 'fls_force_unlock_budget_simulator_button', 999);
function fls_force_unlock_budget_simulator_button()
{
    $target_lesson_ids = array(7236, 7230, 7173, 7167, 7161, 7155, 7149, 6898, 6904, 4137, 7240, 7238, 7234, 7232, 7177, 7175, 7171, 7169, 7165, 7163, 7159, 7157, 7153, 7151, 6902, 6900, 8059);
    if (
        is_singular('sfwd-lessons')
        && in_array(get_the_ID(), $target_lesson_ids, true)
    ) {
        ?>
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        var postId = <?php echo get_the_ID(); ?>;
                        var ajaxUrl = "<?php echo admin_url('admin-ajax.php'); ?>";

                        window.flsForceMarkComplete = function (e) {
                            if (e) {
                                e.preventDefault();
                                e.stopPropagation();
                            }
                            var btn = document.querySelector('.learndash_mark_complete_button');
                            if (btn) {
                                if (btn.tagName === 'INPUT') { btn.value = 'Completing...'; }
                                else { btn.innerText = 'Completing...'; }
                            }

                            jQuery.post(ajaxUrl, {
                                action: 'fls_force_mark_complete',
                                post_id: postId
                            }, function (response) {
                                if (response.success && response.data.redirect) {
                                    window.location.href = response.data.redirect;
                                } else {
                                    console.log('Redirect URL missing');
                                }
                            }).fail(function (xhr) {

                                console.log(xhr.responseText);

                            });
                        };

                        var hasQuiz = document.querySelector('.flq-container');
                        if (hasQuiz) {
                            // This is a custom quiz lesson. Do not auto-unlock the button.
                            // quiz.js will call window.flsForceMarkComplete() when the quiz is finished.
                            return;
                        }

                        setInterval(function () {
                            var btn = document.querySelector('#sfwd-mark-complete input[type="submit"], .learndash_mark_complete_button');

                            // Do not hijack "Mark Incomplete" buttons
                            if (btn && btn.value && btn.value.toLowerCase().indexOf('incomplete') !== -1) {
                                return;
                            }

                            if (btn) {
                                btn.disabled = false;
                                btn.style.opacity = '1';
                                btn.style.cursor = 'pointer';
                                btn.style.pointerEvents = 'auto';

                                var wrapper = btn.closest('.ld-navigation__progress-mark-complete');
                                if (wrapper) wrapper.style.pointerEvents = 'auto';

                                var tooltip = document.querySelector('.ld-tooltip__text');
                                if (tooltip) tooltip.style.display = 'none';

                                if (!btn.dataset.flsAttached) {
                                    btn.dataset.flsAttached = "1";

                                    // Button click block
                                    jQuery(btn).off('click.fls').on('click.fls', function (e) {

                                        e.preventDefault();
                                        e.stopPropagation();

                                        window.flsForceMarkComplete(e);

                                        return false;
                                    });

                                    // Form submit block
                                    var form = btn.closest('form');

                                    if (form) {

                                        jQuery(form).off('submit.fls').on('submit.fls', function (e) {

                                            e.preventDefault();
                                            e.stopPropagation();

                                            window.flsForceMarkComplete(e);

                                            return false;
                                        });

                                    }
                                }
                            }
                            var timer = document.querySelector('.learndash_timer');
                            if (timer) {
                                timer.style.display = 'none';
                            }
                        }, 3000);
                    });
                </script>
                <?php
    }
}

add_action('wp_ajax_fls_force_mark_complete', 'fls_force_mark_complete_ajax');
function fls_force_mark_complete_ajax()
{
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if (!$post_id || !is_user_logged_in()) {
        wp_send_json_error('Invalid request');
    }

    $user_id = get_current_user_id();
    $course_id = learndash_get_course_id($post_id);

    // Mark complete
    learndash_process_mark_complete(
        $user_id,
        $post_id,
        false,
        $course_id,
        true
    );

    /**
     * VERY IMPORTANT
     * Clear LearnDash progression cache
     */
    clean_user_cache($user_id);

    wp_cache_delete($user_id, 'user_meta');

    $redirect_url = fls_get_next_lesson_url_after_completion(
        $course_id,
        $post_id,
        $user_id
    );

    wp_send_json_success(array(
        'redirect' => $redirect_url
    ));
}

/**
 * Redirect logged-out users from course page
 * to Welcome Back page
 */
add_action('template_redirect', 'fls_protect_course_page');

function fls_protect_course_page()
{

    // Exact course page slug
    if (is_singular('sfwd-courses')) {

        global $post;

        // Target only this course
        if ($post && $post->post_name === 'financial-life-skills') {

            // If user NOT logged in
            if (!is_user_logged_in()) {

                wp_redirect(site_url('/welcome-back/'));
                exit;
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Confirm Email Validation
|--------------------------------------------------------------------------
*/

add_action('woocommerce_checkout_process', 'fls_validate_confirm_emails');

function fls_validate_confirm_emails()
{

    /*
    |--------------------------------------------------------------------------
    | Parent Email Validation
    |--------------------------------------------------------------------------
    */

    $billing_email = sanitize_email($_POST['billing_email'] ?? '');
    $billing_email_confirm = sanitize_email($_POST['billing_email_confirm'] ?? '');

    if (
        !empty($billing_email) &&
        !empty($billing_email_confirm) &&
        $billing_email !== $billing_email_confirm
    ) {

        wc_add_notice(
            __('Parent email addresses do not match.', 'woocommerce'),
            'error'
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Student Email Validation
    |--------------------------------------------------------------------------
    */

    $student_email = sanitize_email($_POST['billing_student_email'] ?? '');
    $student_email_confirm = sanitize_email($_POST['billing_student_email_confirm'] ?? '');

    if (
        !empty($student_email) &&
        !empty($student_email_confirm) &&
        $student_email !== $student_email_confirm
    ) {

        wc_add_notice(
            __('Student email addresses do not match.', 'woocommerce'),
            'error'
        );

    }

}


/**
 * Prevent duplicate active LearnDash enrollments
 * WooCommerce Product ID: 3848
 * LearnDash Course ID: 4137
 */

add_action('woocommerce_after_checkout_validation', 'fls_prevent_duplicate_active_enrollment', 10, 2);

function fls_prevent_duplicate_active_enrollment($data, $errors)
{

    // Product ID
    $target_product_id = 3848;

    // LearnDash Course ID
    $target_course_id = 4137;

    // Get billing email
    $email = isset($data['billing_student_email']) ? sanitize_email($data['billing_student_email']) : '';

    if (empty($email)) {
        return;
    }

    // Check if target product exists in cart
    $product_found = false;

    foreach (WC()->cart->get_cart() as $cart_item) {

        if ($cart_item['product_id'] == $target_product_id) {
            $product_found = true;
            break;
        }
    }

    // Stop if target product not in cart
    if (!$product_found) {
        return;
    }

    // Find existing user
    $user = get_user_by('email', $email);

    // If no user exists, allow checkout
    if (!$user) {
        return;
    }

    $user_id = $user->ID;

    /**
     * -------------------------------------------------
     * CHECK ACTIVE PAID ACCESS ONLY
     * -------------------------------------------------
     */

    $is_enrolled = sfwd_lms_has_access(
        $target_course_id,
        $user_id
    );

    /**
     * Trial user check
     */
    $is_trial_user = get_user_meta(
        $user_id,
        'fls_trial_used',
        true
    );

    /**
     * If user has active access
     */
    if ($is_enrolled) {

        /**
         * Trial users CAN upgrade to paid
         */
        if ($is_trial_user === 'yes') {

            return;
        }

        /**
         * Paid users CANNOT purchase again
         */
        $message = 'You already have active access to this programme. Please <a href="' . esc_url(home_url('/welcome-back/')) . '">log into your account</a> to continue learning.';

        $errors->add(
            'duplicate_active_course',
            wp_kses_post($message)
        );
    }
}


/**
 * -------------------------------------------------------
 * Customize Razorpay Checkout Labels & Description
 * -------------------------------------------------------
 */

/**
 * Change Razorpay gateway title
 */
add_filter('woocommerce_gateway_title', 'fls_customize_razorpay_gateway_title', 10, 2);

function fls_customize_razorpay_gateway_title($title, $gateway_id)
{

    if ($gateway_id === 'razorpay') {

        $title = 'UPI, Net Banking, Cards';
    }

    return $title;
}


/**
 * Change Razorpay checkout description dynamically
 */
add_action('wp_footer', 'fls_customize_razorpay_checkout_text');

function fls_customize_razorpay_checkout_text()
{

    if (!is_checkout()) {
        return;
    }
    ?>

        <script>
            jQuery(function ($) {

                function updateRazorpayCheckoutText() {

                    /**
                     * Update description text
                     */
                    $('.payment_method_razorpay .payment_box')
                        .find('p')
                        .each(function () {

                            let text = $(this).text().trim();

                            if (
                                text.includes('Pay securely via Razorpay')
                            ) {

                                $(this).text(
                                    'Pay securely using UPI, Net Banking, Cards.'
                                );
                            }
                        });
                }

                /**
                 * Initial page load
                 */
                updateRazorpayCheckoutText();

                /**
                 * WooCommerce checkout refresh
                 */
                $(document.body).on('updated_checkout', function () {

                    updateRazorpayCheckoutText();
                });

            });
        </script>

        <?php
}

/**
 * EMAIL TEMPLATES
 */
require_once get_stylesheet_directory() . '/inc/email-templates/email-wrapper.php';

require_once get_stylesheet_directory() . '/inc/email-templates/expiry-warning.php';

require_once get_stylesheet_directory() . '/inc/email-templates/expiry-ended.php';

require_once get_stylesheet_directory() . '/inc/email-templates/course-expiry-cron.php';


/**
 * Remove "Billing" word from WooCommerce checkout error messages
 */
add_filter('woocommerce_checkout_required_field_notice', 'fls_custom_required_field_notice', 10, 2);

function fls_custom_required_field_notice($notice, $field_label)
{
    // Remove Billing word
    $field_label = str_replace('Billing ', '', $field_label);

    // Remove Shipping word (optional)
    $field_label = str_replace('Shipping ', '', $field_label);

    return sprintf(
        __('%s is a required field.', 'woocommerce'),
        '<strong>' . esc_html($field_label) . '</strong>'
    );
}

/**
 * Load Roboto Font in WooCommerce Emails
 */
add_action('woocommerce_email_header', 'fls_load_roboto_email_font', 1);

function fls_load_roboto_email_font()
{

    echo '
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    ';
}

/**
 * Force Roboto Font in WooCommerce Emails
 */
add_filter('woocommerce_email_styles', 'fls_custom_woocommerce_email_font', 9999, 2);

function fls_custom_woocommerce_email_font($css, $email)
{
    $custom_css = '

        #body_content,
        #body_content_inner,
        .td,
        .text,
        body,
        table,
        td,
        p,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        a,
        span,
        strong,
        b,
        th {

            font-family: "Roboto", Arial, Helvetica, sans-serif !important;

        }

        h1,
        h2,
        h3 {
            font-family: "Roboto", Arial, Helvetica, sans-serif !important;
            font-weight: 700 !important;
        }

        table td,
        table th {
            font-family: "Roboto", Arial, Helvetica, sans-serif !important;
        }

    ';

    return $css . $custom_css;
}

// ==============================================================================
// B2B Welcome Email (Triggered on LearnDash Group Access)
// ==============================================================================

add_action('ld_added_group_access', 'fls_send_b2b_welcome_email', 10, 2);

function fls_send_b2b_welcome_email($user_id, $group_id)
{
    if (!$user_id) {
        return;
    }

    if (get_user_meta($user_id, '_fls_welcome_email_sent', true)) {
        return;
    }

    $user = get_userdata($user_id);
    if (!$user) {
        return;
    }

    $redirect_to = home_url('/courses/financial-life-skills');

    $magic_link = '';
    if (function_exists('fls_get_magic_login_link')) {
        $magic_link = fls_get_magic_login_link($user, $redirect_to);
    }

    if (!$magic_link) {
        return;
    }

    $subject = 'Welcome to the Financial Life Skills program';
    $email = $user->user_email;
    $body = fls_get_b2b_magic_link_welcome_email_body($user, $email, $magic_link);
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: Financial Life Skills <hello@financiallifeskills.com>',
    );

    wp_mail($email, $subject, $body, $headers);
    update_user_meta($user_id, '_fls_welcome_email_sent', current_time('mysql'));
}

function fls_get_b2b_magic_link_welcome_email_body($user, $email, $magic_link)
{
    $display_name = $user->first_name ? $user->first_name : $user->display_name;

    $message = '<div style="font-family:Arial,Helvetica,sans-serif;color:#1f2937;line-height:1.7;max-width:650px;margin:0 auto;">';

    $message .= '<p style="font-size:22px;font-weight:700;margin-bottom:20px;">
        Hi ' . esc_html($display_name) . ',
    </p>';

    $message .= '<p style="font-size:16px;margin-bottom:20px;">
        Welcome to the Financial Life Skills program! It covers the practical money skills you will need at university and after.
    </p>';

    $message .= '<p style="font-size:16px;font-weight:600;margin-bottom:10px;">
        A few things to know before you begin:
    </p>';

    $message .= '
    <ul style="font-size:16px;line-height:1.9;padding-left:22px;margin-top:0;margin-bottom:28px;">
        <li>
            The program has two parts — a Foundation Module and a Country Module — and takes around 90 minutes to complete.
        </li>
        <li>
            The budget simulator works on desktop or laptop only, so make sure you are on one when you reach that section.
        </li>
        <li>
            Your access runs until the end of this academic year.
        </li>
        <li>
            Still exploring college options? You can change countries as many times as you like.
        </li>
    </ul>
    ';

    $message .= '
    <div style="text-align:left;margin:40px 0;">
        <a href="' . esc_url($magic_link) . '" 
        style="
            display:inline-block;
            background:#0f766e;
            color:#ffffff;
            padding:18px 36px;
            border-radius:10px;
            text-decoration:none;
            font-size:16px;
            font-weight:700;
        ">
            Auto Login Button
        </a>
    </div>
    ';

    $message .= '<p style="font-size:16px;margin-bottom:20px;">
        This login link works once. Next time, head to <a href="https://www.financiallifeskills.com">www.financiallifeskills.com</a> and click the Login in the header to request a new link.
    </p>';

    $message .= '<p style="font-size:16px;margin-bottom:20px;">
        We sent this message to <strong>' . esc_html($email) . '</strong>.<br>
        Keep this email address handy — you will use it every time you return to the program.
    </p>';

    $message .= '<p style="font-size:16px;margin-bottom:20px;">
        See you inside,<br>
        <strong>The Financial Life Skills Team</strong>
    </p>';

    $message .= '</div>';

    if (function_exists('fls_get_premium_email_template')) {
        $message = fls_get_premium_email_template($message);
    } elseif (function_exists('fls_get_email_wrapper')) {
        $message = fls_get_email_wrapper($message);
    }

    return $message;
}


/**
 * Fix for Confetti not triggering on LearnDash Completion (Cache-Proof)
 */
add_action('wp_enqueue_scripts', 'custom_enqueue_confetti_for_learndash', 999);
function custom_enqueue_confetti_for_learndash()
{
    if (function_exists('WPS_Confetti')) {
        WPS_Confetti()->enqueue_scripts();
    }
}

// Set Confetti Cookie ONLY when the LAST LESSON of the course is completed
function custom_check_if_last_lesson_completed($data = null)
{
    $user_id = 0;
    $course_id = 0;
    $lesson_id = 0;

    if (is_array($data)) {
        $user_id = isset($data['user']->ID) ? (int) $data['user']->ID : (isset($data['user_id']) ? (int) $data['user_id'] : get_current_user_id());
        $course_id = isset($data['course']->ID) ? (int) $data['course']->ID : (isset($data['course_id']) ? (int) $data['course_id'] : 0);
        $lesson_id = isset($data['lesson']->ID) ? (int) $data['lesson']->ID : (isset($data['lesson_id']) ? (int) $data['lesson_id'] : 0);
    } elseif (is_numeric($data)) {
        $user_id = get_current_user_id();
        $lesson_id = (int) $data;
    }

    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return;
    }

    if (!$course_id && $lesson_id && function_exists('learndash_get_course_id')) {
        $course_id = (int) learndash_get_course_id($lesson_id);
    }

    if (!$course_id) {
        $course_id = 4137; // Default FLS Course ID
    }

    $is_last_completed = false;

    // 1. Check against the actual valid sequence for this user's chosen country
    if (function_exists('fls_get_valid_lesson_sequence')) {
        $sequence = fls_get_valid_lesson_sequence($course_id, $user_id);
        if (!empty($sequence) && is_array($sequence)) {
            $last_lesson_id = (int) end($sequence);
            if ($lesson_id && (int) $lesson_id === $last_lesson_id) {
                $is_last_completed = true;
            } else {
                // Check if all lessons in sequence are now marked complete
                $all_done = true;
                foreach ($sequence as $seq_id) {
                    if ((int) $seq_id === (int) $lesson_id) continue;
                    if (!learndash_is_lesson_complete($user_id, (int) $seq_id, $course_id)) {
                        $all_done = false;
                        break;
                    }
                }
                if ($all_done) {
                    $is_last_completed = true;
                }
            }
        }
    }

    // 2. Fallback: Check Course Completion Status
    if (!$is_last_completed && function_exists('learndash_course_status')) {
        $status = learndash_course_status($course_id, $user_id);
        if ('COMPLETE' === strtoupper($status) || 'COMPLETED' === strtoupper($status)) {
            $is_last_completed = true;
        }
    }

    if ($is_last_completed) {
        setcookie('fire_learndash_confetti', 'yes', time() + 3600, COOKIEPATH, COOKIE_DOMAIN);
    }
}

add_action('learndash_course_completed', function () {
    setcookie('fire_learndash_confetti', 'yes', time() + 3600, COOKIEPATH, COOKIE_DOMAIN);
});
add_action('learndash_lesson_completed', 'custom_check_if_last_lesson_completed', 10, 1);
add_action('flq_lesson_marked_complete', function ($user_id, $lesson_id, $course_id) {
    custom_check_if_last_lesson_completed(array(
        'user_id' => $user_id,
        'lesson_id' => $lesson_id,
        'course_id' => $course_id,
    ));
}, 10, 3);

add_action('wp_footer', 'custom_learndash_confetti_js_trigger', 999);
function custom_learndash_confetti_js_trigger()
{
    ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Function to check and fire confetti along with the text
                function triggerConfetti() {
                    // 1. Fire Confetti
                    if (typeof wps_launch_confetti_cannon === 'function') {
                        wps_launch_confetti_cannon();
                    } else if (typeof document.dispatchEvent === 'function') {
                        document.dispatchEvent(new CustomEvent('confetti'));
                    }

                    // 2. Show "Course Completed" text overlay
                    var overlay = document.createElement('div');
                    overlay.innerHTML = 'Course Completed! 🎉';
                    overlay.style.position = 'fixed';
                    overlay.style.top = '50%';
                    overlay.style.left = '50%';
                    overlay.style.transform = 'translate(-50%, -50%)';
                    overlay.style.zIndex = '999999';
                    overlay.style.fontSize = '1.5rem';
                    overlay.style.fontWeight = 'bold';
                    overlay.style.color = '#fff';
                    overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.38)';
                    overlay.style.padding = '20px 40px';
                    overlay.style.borderRadius = '15px';
                    overlay.style.boxShadow = '0 10px 30px rgba(70, 70, 70, 0.3)';
                    overlay.style.opacity = '0';
                    overlay.style.transition = 'opacity 0.5s ease';
                    overlay.style.pointerEvents = 'none'; // So it doesn't block clicks
                    overlay.style.textAlign = 'center';

                    document.body.appendChild(overlay);

                    // Fade In
                    setTimeout(function () {
                        overlay.style.opacity = '1';
                    }, 50);

                    // Fade Out and Remove after 3.5 seconds (matching confetti time)
                    setTimeout(function () {
                        overlay.style.opacity = '0';
                        setTimeout(function () {
                            if (document.body.contains(overlay)) {
                                document.body.removeChild(overlay);
                            }
                        }, 500);
                    }, 3500);
                }

                // 1. Check for cookie on page load OR ?completed=1 query parameter
                var match = document.cookie.match('(^|;) ?fire_learndash_confetti=([^;]*)(;|$)');
                var urlHasCompleted = window.location.search.indexOf('completed=1') !== -1;

                if ((match && match[2] === 'yes') || urlHasCompleted) {
                    document.cookie = 'fire_learndash_confetti=; Max-Age=-99999999; path=/'; // clear cookie
                    setTimeout(triggerConfetti, 500);
                }
            });
        </script>
        <?php
}

/**
 * Display Country Change Count and History on User Profile for Admins Only
 */
add_action('show_user_profile', 'fls_show_country_change_stats_in_profile');
add_action('edit_user_profile', 'fls_show_country_change_stats_in_profile');

function fls_show_country_change_stats_in_profile($user)
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $current_country = get_user_meta($user->ID, 'fls_selected_country_name', true);
    $current_code = get_user_meta($user->ID, 'fls_selected_country_code', true);
    $change_count = (int) get_user_meta($user->ID, 'fls_country_change_count', true);
    $history = get_user_meta($user->ID, 'fls_country_change_history', true);
    ?>
    <div style="margin-top: 25px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; border-left: 4px solid #3EAA6D; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,.05);">
        <h2 style="margin-top: 0; color: #152238; font-size: 1.3em;">
            🌍 Country Selection Tracking <span style="font-size: 12px; color: #666; font-weight: normal;">(Admin Only)</span>
        </h2>
        <table class="form-table" role="presentation" style="margin-top: 10px;">
            <tr>
                <th scope="row" style="width: 250px;"><strong>Currently Selected Country:</strong></th>
                <td>
                    <?php if (!empty($current_country)): ?>
                        <span style="display: inline-block; padding: 4px 12px; background: #e6f4ea; color: #137333; font-weight: 600; border-radius: 4px; font-size: 14px;">
                            <?php echo esc_html($current_country); ?> (<?php echo esc_html($current_code); ?>)
                        </span>
                    <?php else: ?>
                        <span style="color: #888;">Not selected yet</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><strong>Total Times Country Changed / Selected:</strong></th>
                <td>
                    <span style="font-size: 18px; font-weight: 700; color: #152238;">
                        <?php echo esc_html($change_count); ?> time(s)
                    </span>
                </td>
            </tr>
            <?php if (!empty($history) && is_array($history)): ?>
            <tr>
                <th scope="row"><strong>Country Change History:</strong></th>
                <td>
                    <table style="width: 100%; max-width: 600px; border-collapse: collapse; background: #f9f9f9; border: 1px solid #e5e5e5; font-size: 13px;">
                        <thead>
                            <tr style="background: #ececec; text-align: left;">
                                <th style="padding: 8px 12px; border-bottom: 1px solid #ddd;">#</th>
                                <th style="padding: 8px 12px; border-bottom: 1px solid #ddd;">Country</th>
                                <th style="padding: 8px 12px; border-bottom: 1px solid #ddd;">Code</th>
                                <th style="padding: 8px 12px; border-bottom: 1px solid #ddd;">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach (array_reverse($history) as $item):
                                ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 8px 12px;"><?php echo esc_html($i++); ?></td>
                                <td style="padding: 8px 12px; font-weight: 600; color: #152238;"><?php echo esc_html(isset($item['country_name']) ? $item['country_name'] : '-'); ?></td>
                                <td style="padding: 8px 12px;"><?php echo esc_html(isset($item['country_code']) ? $item['country_code'] : '-'); ?></td>
                                <td style="padding: 8px 12px; color: #555;"><?php echo esc_html(isset($item['timestamp']) ? $item['timestamp'] : '-'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    <?php
}


/**
 * Financial Life Skills
 * Elementor Account Dropdown + Secure Logout
 */


/* =========================================================
 * 1. SECURE LOGOUT HANDLER
 * ========================================================= */

add_action( 'admin_post_fls_secure_logout', 'fls_secure_logout' );
add_action( 'admin_post_nopriv_fls_secure_logout', 'fls_secure_logout' );

function fls_secure_logout() {

    /*
     * Verify nonce.
     */
    if (
        ! isset( $_GET['_wpnonce'] ) ||
        ! wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ),
            'fls_secure_logout'
        )
    ) {
        wp_die(
            'Security verification failed. Please try logging out again.',
            'Logout Error',
            array(
                'response' => 403,
            )
        );
    }

    /*
     * Proper WordPress logout.
     */
    wp_logout();

    /*
     * Clear authentication cookie.
     */
    wp_clear_auth_cookie();

    /*
     * Redirect to homepage.
     */
    wp_safe_redirect( home_url( '/' ) );

    exit;
}


/* =========================================================
 * 2. ELEMENTOR ACCOUNT DROPDOWN
 * ========================================================= */

add_action( 'wp_footer', 'fls_elementor_account_dropdown', 99 );

function fls_elementor_account_dropdown() {

    /*
     * Don't run in WordPress admin.
     */
    if ( is_admin() && ! wp_doing_ajax() ) {
        return;
    }


    /*
     * Don't modify Elementor editor preview.
     */
    if (
        class_exists( '\Elementor\Plugin' ) &&
        isset( \Elementor\Plugin::$instance->editor ) &&
        \Elementor\Plugin::$instance->editor->is_edit_mode()
    ) {
        return;
    }


    /* =====================================================
     * URLs
     * ===================================================== */

    $login_url   = home_url( '/welcome-back/' );
    $account_url = home_url( '/my-account/' );


    /* =====================================================
     * USER INFORMATION
     * ===================================================== */

    $user_name = '';

    if ( is_user_logged_in() ) {

        $current_user = wp_get_current_user();

        /*
         * First Name preferred.
         */
        $user_name = trim( $current_user->first_name );

        /*
         * If First Name is empty,
         * use Display Name.
         */
        if ( empty( $user_name ) ) {
            $user_name = $current_user->display_name;
        }

        /*
         * Final fallback.
         */
        if ( empty( $user_name ) ) {
            $user_name = $current_user->user_login;
        }
    }

    $logout_nonce = wp_create_nonce( 'fls_secure_logout' );

    $logout_url = add_query_arg(
        array(
            'action'   => 'fls_secure_logout',
            '_wpnonce' => $logout_nonce,
        ),
        admin_url( 'admin-post.php' )
    );


    ?>

    <style>

        /* =====================================================
         * ACCOUNT MENU
         * ===================================================== */

        .fls-account-menu .elementor-icon-list-items {
            overflow: visible !important;
        }

        .fls-account-menu .elementor-icon-list-item {
            position: relative;
        }


        /* =====================================================
         * USER MENU
         * ===================================================== */

        .fls-account-menu .fls-user-menu {
            position: relative;
            display: flex !important;
            align-items: center;
        }


        /* User toggle */

        .fls-account-menu .fls-user-menu-toggle {
            display: inline-flex !important;
            align-items: center;
            gap: 8px;

            text-decoration: none !important;
            cursor: pointer;

            white-space: nowrap;
        }


        /* User icon */

        .fls-account-menu .fls-user-icon {
            width: 18px;
            height: 18px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            flex: 0 0 18px;
        }

        .fls-account-menu .fls-user-icon svg {
            width: 18px;
            height: 18px;

            display: block;
        }


        /* User name */

        .fls-account-menu .fls-user-name {
            line-height: 1.2;
        }


        /* Chevron */

        .fls-account-menu .fls-user-chevron {
            width: 7px;
            height: 7px;

            border-right: 1.5px solid currentColor;
            border-bottom: 1.5px solid currentColor;

            transform: rotate(45deg);

            margin-left: 2px;
            margin-top: -4px;

            transition: transform .2s ease;
        }


        /* =====================================================
         * DROPDOWN
         * ===================================================== */

        .fls-account-menu .fls-user-dropdown {

            position: absolute;

            top: calc(100% + 12px);
            right: 0;

            min-width: 170px;

            background: #ffffff;

            border: 1px solid rgba(0, 0, 0, 0.08);

            border-radius: 10px;

            box-shadow:
                0 12px 35px rgba(20, 25, 60, 0.14);

            padding: 8px;

            opacity: 0;
            visibility: hidden;

            transform: translateY(8px);

            transition:
                opacity .2s ease,
                visibility .2s ease,
                transform .2s ease;

            z-index: 999999;
        }


        /*
         * Invisible bridge.
         */
        .fls-account-menu .fls-user-dropdown::before {

            content: "";

            position: absolute;

            left: 0;
            right: 0;

            top: -14px;

            height: 14px;
        }


        /* Desktop hover */

        .fls-account-menu .fls-user-menu:hover .fls-user-dropdown,
        .fls-account-menu .fls-user-menu.fls-open .fls-user-dropdown {

            opacity: 1;

            visibility: visible;

            transform: translateY(0);
        }


        /* Chevron animation */

        .fls-account-menu .fls-user-menu:hover .fls-user-chevron,
        .fls-account-menu .fls-user-menu.fls-open .fls-user-chevron {

            transform: rotate(225deg);

            margin-top: 4px;
        }


        /* =====================================================
         * DROPDOWN LINKS
         * ===================================================== */

        .fls-account-menu .fls-user-dropdown a {

            display: flex !important;
            align-items: center;

            width: 100%;

            padding: 10px 12px;

            color: #1b1d3a !important;

            text-decoration: none !important;

            font-size: 14px;

            font-weight: 500;

            border-radius: 7px;

            transition:
                background-color .2s ease,
                color .2s ease;
        }


        .fls-account-menu .fls-user-dropdown a:hover {

            background: #f3f7f6;

            color: #087f83 !important;
        }


        /* =====================================================
         * MOBILE
         * ===================================================== */

@media (max-width: 767px) {

    /*
     * User menu becomes a vertical block
     * on mobile.
     */
    .fls-account-menu .fls-user-menu {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        width: 100%;
    }


    /*
     * User name / icon row.
     */
    .fls-account-menu .fls-user-menu-toggle {
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px;

        width: auto !important;
    }


    /*
     * Mobile dropdown:
     *
     * IMPORTANT:
     * position: static means it becomes part
     * of the normal layout.
     *
     * Therefore the "Get the overview"
     * button below it will automatically
     * move down when dropdown opens.
     */
    .fls-account-menu .fls-user-dropdown {

        position: static !important;

        top: auto !important;
        right: auto !important;

        width: 170px !important;
        min-width: 170px !important;

        margin-top: 10px !important;
        margin-left: 0 !important;

        transform: none !important;

        /*
         * Keep dropdown left aligned
         * directly below user name.
         */
        align-self: flex-start !important;
    }


    /*
     * When dropdown is closed, it should
     * not occupy any space.
     */
    .fls-account-menu .fls-user-menu:not(.fls-open) .fls-user-dropdown {

        display: none !important;

        opacity: 0;
        visibility: hidden;
    }


    /*
     * When dropdown is open,
     * make it visible and allow it
     * to push content below.
     */
    .fls-account-menu .fls-user-menu.fls-open .fls-user-dropdown {

        display: block !important;

        opacity: 1;
        visibility: visible;

        transform: none !important;
    }


    /*
     * Remove the desktop invisible bridge
     * on mobile because dropdown is now
     * in normal document flow.
     */
    .fls-account-menu .fls-user-dropdown::before {

        display: none !important;
    }

}

    </style>


    <script>

    document.addEventListener('DOMContentLoaded', function () {

        /*
         * =====================================================
         * PHP DATA
         * =====================================================
         */

        const isLoggedIn =
            <?php echo is_user_logged_in() ? 'true' : 'false'; ?>;

        const loginUrl =
            <?php echo wp_json_encode( $login_url ); ?>;

        const accountUrl =
            <?php echo wp_json_encode( $account_url ); ?>;

        /*
         * IMPORTANT:
         * This URL is generated using wp_create_nonce()
         * and add_query_arg(), so it contains a real
         * "&_wpnonce=" instead of "&amp;_wpnonce=".
         */
        const logoutUrl =
            <?php echo wp_json_encode( $logout_url ); ?>;

        const userName =
            <?php echo wp_json_encode( $user_name ); ?>;

        const menus =
            document.querySelectorAll(
                '.fls-account-menu'
            );

        if (!menus.length) {
            return;
        }


        menus.forEach(function (menu) {

            /*
             * Find list items.
             */
            const items =
                menu.querySelectorAll(
                    '.elementor-icon-list-item'
                );

            if (!items.length) {
                return;
            }


            /*
             * Find Login item.
             */
            let loginItem = null;

            items.forEach(function (item) {

                const link =
                    item.querySelector('a');

                if (!link) {
                    return;
                }

                const text =
                    link.textContent
                        .trim()
                        .toLowerCase();

                if (text === 'login') {
                    loginItem = item;
                }

            });


            /*
             * If Login doesn't exist,
             * stop.
             */
            if (!loginItem) {
                return;
            }

            if (!isLoggedIn) {

                loginItem.classList.add(
                    'fls-login-item'
                );

                const loginLink =
                    loginItem.querySelector('a');

                if (loginLink) {
                    loginLink.href = loginUrl;
                }

                return;
            }

            const userItem =
                document.createElement('li');

            userItem.className =
                'elementor-icon-list-item fls-user-menu';


            /*
             * User icon.
             */
            const userIcon = `
                <span
                    class="fls-user-icon"
                    aria-hidden="true"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <circle
                            cx="12"
                            cy="8"
                            r="3.5"
                            stroke="currentColor"
                            stroke-width="1.8"
                        />

                        <path
                            d="M5.5 20C6.2 15.9 8.4 14 12 14C15.6 14 17.8 15.9 18.5 20"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        />
                    </svg>
                </span>
            `;


            /*
             * User toggle.
             */
            const userToggle =
                document.createElement('a');

            userToggle.href =
                accountUrl;

            userToggle.className =
                'fls-user-menu-toggle';

            userToggle.setAttribute(
                'aria-haspopup',
                'true'
            );

            userToggle.setAttribute(
                'aria-expanded',
                'false'
            );


            userToggle.innerHTML =
                userIcon +
                '<span class="fls-user-name">' +
                escapeHtml(userName) +
                '</span>' +
                '<span class="fls-user-chevron"></span>';


            /*
             * Dropdown.
             */
            const dropdown =
                document.createElement('div');

            dropdown.className =
                'fls-user-dropdown';


            /*
             * My Account.
             */
            const accountLink =
                document.createElement('a');

            accountLink.href =
                accountUrl;

            accountLink.textContent =
                'My Account';


            /*
             * Logout.
             */
            const logoutLink =
                document.createElement('a');

            logoutLink.href =
                logoutUrl;

            logoutLink.textContent =
                'Log Out';

            logoutLink.className =
                'fls-logout-link';


            /*
             * Add links.
             */
            dropdown.appendChild(
                accountLink
            );

            dropdown.appendChild(
                logoutLink
            );


            /*
             * Add everything.
             */
            userItem.appendChild(
                userToggle
            );

            userItem.appendChild(
                dropdown
            );


            /*
             * Replace Login.
             */
            loginItem.parentNode.replaceChild(
                userItem,
                loginItem
            );


            userToggle.addEventListener(
    'click',
    function (event) {

        if (
            window.matchMedia(
                '(hover: none)'
            ).matches
        ) {

            /*
             * If dropdown is CLOSED:
             * open it and prevent navigation.
             */
            if (
                !userItem.classList.contains(
                    'fls-open'
                )
            ) {

                event.preventDefault();

                userItem.classList.add(
                    'fls-open'
                );

                userToggle.setAttribute(
                    'aria-expanded',
                    'true'
                );

            }

            /*
             * If dropdown is already OPEN:
             * close it.
             */
            else {

                event.preventDefault();

                userItem.classList.remove(
                    'fls-open'
                );

                userToggle.setAttribute(
                    'aria-expanded',
                    'false'
                );

            }

        }

    }
);

        });

        document.addEventListener(
            'click',
            function (event) {

                document
                    .querySelectorAll(
                        '.fls-user-menu.fls-open'
                    )
                    .forEach(function (menu) {

                        if (
                            !menu.contains(
                                event.target
                            )
                        ) {

                            menu.classList.remove(
                                'fls-open'
                            );

                            const toggle =
                                menu.querySelector(
                                    '.fls-user-menu-toggle'
                                );

                            if (toggle) {

                                toggle.setAttribute(
                                    'aria-expanded',
                                    'false'
                                );

                            }

                        }

                    });

            }
        );


        function escapeHtml(value) {

            return String(value)

                .replace(
                    /&/g,
                    '&amp;'
                )

                .replace(
                    /</g,
                    '&lt;'
                )

                .replace(
                    />/g,
                    '&gt;'
                )

                .replace(
                    /"/g,
                    '&quot;'
                )

                .replace(
                    /'/g,
                    '&#039;'
                );

        }

    });

    </script>

    <?php
}