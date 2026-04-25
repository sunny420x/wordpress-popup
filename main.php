<?php
/**
 * Plugin Name: Sunny's Image Popup with Cookie
 * Description: แสดง Pop-up รูปภาพพร้อมปุ่มปิด และจำ Cookie 7 วัน
 * Version: 1.0
 * Author: Jirakit Pawnsakunrungrot
 * Author URI: https://www.linkedin.com/in/sunny-jirakit
 * Plugin URI: https://github.com/sunny420x/wordpress-image-popup
 */

if (!defined('ABSPATH'))
    exit;

add_action('wp_footer', 'wgc_render_image_popup');
add_action('admin_menu', 'popup_menu');

function popup_menu()
{
    add_menu_page(
        'Popup Settings', // Title ของหน้า
        'ระบบ Popup', // ชื่อเมนูที่โชว์ในแถบข้าง
        'manage_options', //สิทธิ์การเข้าถึง (Admin)
        'wordpress-popup-settings', // Slug ของหน้า
        'popup_settings_page', // ฟังก์ชันที่ใช้พ่น HTML หน้า Setting
        'dashicons-admin-tools', // ไอคอน
        '80' // ตำแหน่งเมนู
    );
}


function popup_settings_page()
{
    $profiles = get_option('popup_profiles', array(
        [
            'name' => 'default',
            'enable' => 'yes',
            'cookie_days_to_expire' => 7,
            'image_url' => '',
            'link_url' => '#',
            'cookie_name' => 'popup_expire_in',
            'width' => 50
        ]
    ));

    if ( isset($_GET['saveProfile']) ) {
        
        //ดึงข้อมูลทั้งหมดที่มีอยู่ตอนนี้มาก่อน
        $profiles = get_option('popup_profiles', array());
        $profile_name_to_find = $_GET['saveProfile'];
        $found = false;

        // เปลี่ยนตัวอื่นให้เป็น "no"
        if ($_POST['popup_enable'] === 'yes' ) {
            foreach ( $profiles as &$p ) {
                $p['enable'] = 'no';
            }
            unset($p);
        }

        foreach ( $profiles as &$profile ) {
            if ( $profile['name'] === $profile_name_to_find ) {
                
                // อัปเดตค่าจากฟอร์มลงไปใน Array
                $profile['name']                 = sanitize_text_field($_POST['popup_name']);
                $profile['cookie_days_to_expire'] = intval($_POST['popup_cookie_days_to_expire']);
                $profile['enable']               = sanitize_text_field($_POST['popup_enable']);
                $profile['image_url']            = esc_url_raw($_POST['popup_image_url']);
                $profile['link_url']             = esc_url_raw($_POST['popup_link_url']);
                $profile['cookie_name']          = sanitize_title($_POST['popup_cookie_name']);
                $profile['width']          = sanitize_title($_POST['width']);
                
                $found = true;
                break; 
            }
        }

        if ( ! $found ) {
            $profiles[] = array(
                'name'                 => sanitize_text_field($_POST['popup_name']),
                'cookie_days_to_expire' => intval($_POST['popup_cookie_days_to_expire']),
                'enable'               => sanitize_text_field($_POST['popup_enable']),
                'image_url'            => esc_url_raw($_POST['popup_image_url']),
                'link_url'             => esc_url_raw($_POST['popup_link_url']),
                'cookie_name'          => sanitize_title($_POST['popup_cookie_name']),
                'width'          => sanitize_title($_POST['width']),
            );
        }

        update_option('popup_profiles', $profiles);
        $profile_name = sanitize_text_field($_POST['popup_name']);
        wp_redirect( admin_url("admin.php?page=wordpress-popup-settings&profile=$profile_name") );
        exit;
    }

    if(isset($_GET['newProfile'])) {
        $profiles[] = array(
            'name'                 => sanitize_text_field($_POST['popup_name']),
            'cookie_days_to_expire' => intval($_POST['popup_cookie_days_to_expire']),
            'enable'               => sanitize_text_field($_POST['popup_enable']),
            'image_url'            => esc_url_raw($_POST['popup_image_url']),
            'link_url'             => esc_url_raw($_POST['popup_link_url']),
            'cookie_name'          => sanitize_title($_POST['popup_cookie_name']),
            'width'          => sanitize_title($_POST['width']),
        );

        // เปลี่ยนตัวอื่นให้เป็น "no"
        if ($_POST['popup_enable'] === 'yes' ) {
            foreach ( $profiles as &$p ) {
                $p['enable'] = 'no';
            }
            unset($p);
        }

        update_option('popup_profiles', $profiles);
        $profile_name = sanitize_text_field($_POST['popup_name']);
        wp_redirect( admin_url("admin.php?page=wordpress-popup-settings&profile=$profile_name") );
        exit;

    }

    if (isset($_GET['deleteProfile']) ) {
        $profiles = get_option('popup_profiles', array());
        $target_name = $_GET['deleteProfile'];
        $found = false;

        foreach ( $profiles as $index => $profile ) {
            if ( $profile['name'] === $target_name ) {
                unset($profiles[$index]);
                $found = true;
                break;
            }
        }

        if ( $found ) {
            $profiles = array_values($profiles);
            
            update_option('popup_profiles', $profiles);
            
            wp_redirect( admin_url('admin.php?page=wordpress-popup-settings') );
            exit;
        }
    }
    ?>
    <style>
        ul.popup_profile_list {
            margin-top: 20px;
        }
        ul.popup_profile_list li {
            padding: 10px 20px;
            font-size: 14px;
            background: #f8f8f8;
            color: #111;
            transition: .2s ease-in-out;
        }

        ul.popup_profile_list li:hover {
            background: #eee;
            cursor: pointer;
        }
    </style>
    <div class="wrap" style="background: #fff; padding: 20px; border-radius: 10px; margin-top: 20px;">
        <div style="display: flex;">
            <div style="width: 300px; margin-right: 35px;">
                <a href="/?popup_preview" target="_blank" class="button" style="width: 100%; margin: 10px 0;">👁️ ดูตัวอย่าง Popup ปัจจุบัน</a>
                <a href="admin.php?page=wordpress-popup-settings" class="button" style="width: 100%;">➕ สร้างโปรไฟล์ใหม่</a>
                <h1 style="margin-top: 10px;">📋 โปรไฟล์ Popup ทั้งหมด</h1>
                <ul class="popup_profile_list">
                    <?php
                    foreach ($profiles as $profile) {
                        ?>
                        <li onclick="window.location.href='admin.php?page=wordpress-popup-settings&profile=<?= $profile['name'] ?>'">
                            <?php if($profile['enable'] == "yes") { ?>
                            <span style="color: green; margin-right: 10px;">●</span>
                            <?php } else { ?>
                            <span style="color: red; margin-right: 10px;">●</span><?php } ?> 
                            <?= $profile['name'] ?></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <div>
                <h1>✨ ระบบ Popup สำหรับ Wordpress</h1>
                <p>ระบบ Popup แสดงรูปภาพ เช่น โมษณา สิทธิพิเศษ Artwork เทศกาลต่าง ๆ เป็นต้น ระบบจะแสดงแค่หน้า homepage</p>
                <hr>
                <?php
                $selected_profile = array_find($profiles, function ($profile) {
                    return $profile['name'] === $_GET['profile'];
                });

                if ($_GET['profile'] && $selected_profile != null && $selected_profile != "") {
                    ?>
                    <form action="admin.php?page=wordpress-popup-settings&saveProfile=<?=$selected_profile['name']?>" method="post">
                        <?php
                        settings_fields('popup_settings_group');
                        ?>
                        <h1>✏️ แก้ไขโปรไฟล์: <strong><?=$selected_profile['name']?></strong></h1>
                        <h2>สถานะ Popup:</h2>
                        <select name="popup_enable" id="">
                            <option value="yes" <?php if ($selected_profile['enable'] == "yes") {
                                echo "selected";
                            } ?>>เปิดการใช้งาน
                            </option>
                            <option value="no" <?php if ($selected_profile['enable'] == "no") {
                                echo "selected";
                            } ?>>ปิดการใช้งาน
                            </option>
                        </select>
                        <h2>ชื่อ Popup:</h2>
                        <input type="text" name="popup_name" value="<?php echo esc_attr($selected_profile['name']); ?>"
                            style="width: 400px;" />
                        <h2>จำนวนวันหมดอายุของ Popup:</h2>
                        <input type="number" name="popup_cookie_days_to_expire"
                            value="<?php echo esc_attr($selected_profile['cookie_days_to_expire']); ?>" /> วัน
                        <h2>เลือกรูปภาพ Popup:</h2>
                        <div class="image-upload-wrapper">
                            <input type="text" name="popup_image_url" id="popup_image_url"
                                value="<?php echo esc_attr($selected_profile['image_url']); ?>" style="width: 400px;" />

                            <button type="button" class="button" id="upload_image_button">เลือกรูปภาพ...</button>

                            <div id="image_preview" style="margin-top: 10px;">
                                <?php $banner_url = $selected_profile['image_url']; ?>
                                <?php if ($banner_url): ?>
                                    <img src="<?php echo esc_url($banner_url); ?>"
                                        style="max-width: 300px; border: 1px solid #ccc;" />
                                <?php endif; ?>
                            </div>
                        </div>
                        <h2>ลิงค์ของ Popup:</h2>
                        <input type="text" name="popup_link_url" value="<?php echo esc_attr($selected_profile['link_url']); ?>"
                            style="width: 400px;" />
                        <h2>ความกว้างของ Popup (%):</h2>
                        <input type="number" name="width"
                            value="<?php echo esc_attr($selected_profile['width']); ?>" /> %
                        <h2>ชื่อ Cookie:</h2>
                        <input type="text" name="popup_cookie_name"
                            value="<?php echo esc_attr($selected_profile['cookie_name']); ?>" style="width: 400px;" />
                        <br>
                        <?php submit_button('บันทึกการเปลี่ยนแปลง'); ?>
                        <a href="admin.php?page=wordpress-popup-settings&deleteProfile=<?php echo esc_attr($selected_profile['name']); ?>">ลบ Profile นี้</a>
                    </form>
                    <?php
                } else {
                    ?>
                    <form action="admin.php?page=wordpress-popup-settings&newProfile" method="post">
                        <h1>✏️ สร้างโปรไฟล์ Popup ใหม่</h1>
                        <h2>ชื่อ Popup:</h2>
                        <input type="text" name="popup_name" style="width: 400px;" />
                        <h2>สถานะ Popup:</h2>
                        <select name="popup_enable" id="">
                            <option value="yes">เปิดการใช้งาน
                            </option>
                            <option value="no">ปิดการใช้งาน
                            </option>
                        </select>
                        <h2>จำนวนวันหมดอายุของ Popup:</h2>
                        <input type="number" name="popup_cookie_days_to_expire" /> วัน
                        <h2>เลือกรูปภาพ Popup:</h2>
                        <div class="image-upload-wrapper">
                            <input type="text" name="popup_image_url" id="popup_image_url" style="width: 400px;" />
    
                            <button type="button" class="button" id="upload_image_button">เลือกรูปภาพ...</button>
    
                            <div id="image_preview" style="margin-top: 10px;">
                            </div>
                        </div>
                        <h2>ความกว้างของ Popup (%):</h2>
                        <input type="number" name="width" value="50" /> %
                        <h2>ลิงค์ของ Popup:</h2>
                        <input type="text" name="popup_link_url" value="" style="width: 400px;" />
                        <h2>ชื่อ Cookie:</h2>
                        <input type="text" name="popup_cookie_name" value="" style="width: 400px;" />
                        <?php submit_button('เพิ่มโปรไฟล์'); ?>
                    </form>
                <?php
                }
                ?>
                <hr>
                <p>Github Repository: <a href="https://github.com/sunny420x/wordpress-popup"
                        target="_blank">github.com/sunny420x/wordpress-popup</a></p>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        $('#upload_image_button').click(function (e) {
                            e.preventDefault();

                            // สร้าง Media Frame
                            var image_frame = wp.media({
                                title: 'เลือกรูปภาพ',
                                multiple: false,
                                library: { type: 'image' }
                            });

                            // เมื่อเลือกรูปภาพเสร็จแล้ว
                            image_frame.on('select', function () {
                                var selection = image_frame.state().get('selection').first().toJSON();
                                var image_url = selection.url;

                                // 1. เอา URL ไปใส่ใน Input
                                $('#popup_image_url').val(image_url);

                                // 2. แสดงตัวอย่างรูปภาพ (Preview)
                                $('#image_preview').html('<img src="' + image_url + '" style="max-width: 300px; border: 1px solid #ccc;" />');
                            });

                            image_frame.open();
                        });
                    });
                </script>
            </div>
        </div>
    </div>
    <?php
}

add_action('admin_init', 'popup_settings_init');

function popup_settings_init()
{
    register_setting('popup_settings_group', 'popup_profiles');
}

function wgc_render_image_popup()
{
    if (!is_front_page()) {
        return;
    }

    $profiles = get_option('popup_profiles', array());
    if ( empty( $profiles ) ) return;

    $selected_profile = null;
    foreach ( $profiles as $profile ) {
        if ( isset($profile['enable']) && ($profile['enable'] === 'yes' || isset($_GET['popup_preview'])) ) {
            $selected_profile = $profile;
            break;
        }
    }

    if ( ! $selected_profile ) return;

    $image_url      = $selected_profile['image_url'] ?? '';
    $link_url       = $selected_profile['link_url'] ?? '#';
    $cookie_name    = $selected_profile['cookie_name'] ?? '';
    $popup_width    = $selected_profile['width'] ?? 50;
    $days_to_expire = intval($selected_profile['cookie_days_to_expire'] ?? 7);

    if ( empty( $image_url ) ) return;
    ?>

    <style>
        #wgc-popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 999999;
            justify-content: center;
            align-items: center;
        }

        #wgc-popup-container {
            position: relative;
            max-width: <?=$popup_width;?>%;
            max-height: 90%;
            text-align: center;
        }

        #wgc-popup-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.5);
            cursor: pointer;
        }

        #wgc-popup-close {
            position: absolute;
            top: -10px;
            right: -15px;
            padding: 2px 10px;
            background: #fff;
            border-radius: 50%;
            color: #333;
            font-size: 18px;
            font-weight: bold;
            line-height: 30px;
            cursor: pointer;
            border: 1.3px solid #333;
            transition: 0.3s;
        }

        #wgc-popup-close:hover {
            background: #d63384;
            color: #fff;
        }

        @media screen and (max-width: 1200px) {
            #wgc-popup-container {
                max-width: 90%;
            }
        }
    </style>

    <div id="wgc-popup-overlay">
        <div id="wgc-popup-container">
            <div id="wgc-popup-close">X</div>
            <a href="<?php echo esc_url($link_url); ?>">
                <img src="<?php echo esc_url($image_url); ?>" alt="Promotion">
            </a>
        </div>
    </div>

    <script>
        (function () {
            const popupName = "<?php echo $cookie_name; ?>";
            const expireDays = <?php echo $days_to_expire; ?>;
            const overlay = document.getElementById('wgc-popup-overlay');
            const closeBtn = document.getElementById('wgc-popup-close');

            // ฟังก์ชันอ่าน Cookie
            function getCookie(name) {
                let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
                return match ? match[2] : null;
            }

            // ฟังก์ชันสร้าง Cookie
            function setCookie(name, value, days) {
                let d = new Date();
                d.setTime(d.addDays(days));
                let expires = "expires=" + d.toUTCString();
                document.cookie = name + "=" + value + ";" + expires + ";path=/";
            }

            Date.prototype.addDays = function (days) {
                var date = new Date(this.valueOf());
                date.setDate(date.getDate() + days);
                return date;
            }

            // เช็คว่าเคยปิดไปหรือยัง
            if (!getCookie(popupName)) {
                // แสดงผลหลังจากโหลดหน้าเสร็จ 2 วินาที (เพื่อให้หน้าเว็บโหลดหลักเสร็จก่อน)
                setTimeout(() => {
                    overlay.style.display = 'flex';
                }, 2000);
            }

            // เมื่อกดปุ่มปิด
            closeBtn.onclick = function () {
                overlay.style.display = 'none';
                <?php
                if(!isset($_GET['popup_preview'])) {
                ?>
                setCookie(popupName, "closed", expireDays);
                <?php
                }
                ?>
            };

            // เมื่อกดที่พื้นหลัง (Overlay) ก็ให้ปิดด้วย
            overlay.onclick = function (e) {
                if (e.target === overlay) {
                    closeBtn.click();
                }
            };
        })();
    </script>
    <?php
}