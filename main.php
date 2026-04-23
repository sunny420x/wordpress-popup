<?php
/**
 * Plugin Name: Image Popup with Cookie
 * Description: แสดง Pop-up รูปภาพพร้อมปุ่มปิด และจำ Cookie 7 วัน
 * Version: 1.0
 * Author: Jirakit Pawnsakunrungrot
 * Author URI: https://www.linkedin.com/in/sunny-jirakit
 * Plugin URI: https://github.com/sunny420x/wordpress-popup
 */

if (!defined('ABSPATH'))
    exit;

add_action('wp_footer', 'wgc_render_image_popup');
add_action( 'admin_menu', 'popup_menu' );

function popup_menu() {
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
    ?>
    <div class="wrap" style="background: #fff; padding: 20px; border-radius: 10px; margin-top: 20px;">
        <h1>✨ ระบบ Popup สำหรับ Wordpress</h1>
        <p>ระบบ Popup แสดงรูปภาพ เช่น โมษณา สิทธิพิเศษ Artwork เทศกาลต่าง ๆ เป็นต้น ระบบจะแสดงแค่หน้า homepage</p>
        <hr>
        <form action="options.php" method="post">
            <?php
            settings_fields('popup_settings_group');
            ?>
            <h2>สถานะ Popup:</h2>
            <select name="popup_enable" id="">
                <option value="yes" <?php if(get_option('popup_enable', 'yes') == "yes") {echo "selected";} ?>>เปิดการใช้งาน</option>
                <option value="no" <?php if(get_option('popup_enable', 'yes') == "no") {echo "selected";} ?>>ปิดการใช้งาน</option>
            </select>
            <h2>จำนวนวันหมดอายุของ Popup:</h2>
            <input type="number" name="popup_cookie_days_to_expire"
                value="<?php echo esc_attr(get_option('popup_cookie_days_to_expire', 7)); ?>" /> วัน
            <h2>เลือกรูปภาพ Popup:</h2>
            <div class="image-upload-wrapper">
                <input type="text" name="popup_image_url" id="popup_image_url"
                    value="<?php echo esc_attr(get_option('popup_image_url', '')); ?>" style="width: 400px;" />

                <button type="button" class="button" id="upload_image_button">เลือกรูปภาพ...</button>

                <div id="image_preview" style="margin-top: 10px;">
                    <?php $banner_url = get_option('popup_image_url'); ?>
                    <?php if ($banner_url): ?>
                        <img src="<?php echo esc_url($banner_url); ?>" style="max-width: 300px; border: 1px solid #ccc;" />
                    <?php endif; ?>
                </div>
            </div>
            <h2>ลิงค์ของ Popup:</h2>
            <input type="text" name="popup_link_url"
                value="<?php echo esc_attr(get_option('popup_link_url', '#')); ?>" style="width: 400px;" />
            <h2>ชื่อ Cookie:</h2>
            <input type="text" name="popup_cookie_name"
                value="<?php echo esc_attr(get_option('popup_cookie_name', 'popup_expire_in')); ?>" style="width: 400px;" />
            <br>
            <?php submit_button('บันทึกการเปลี่ยนแปลง'); ?>
            <hr>
            <p>Github Repository: <a href="https://github.com/sunny420x/wordpress-popup"
                    target="_blank">github.com/sunny420x/wordpress-popup</a></p>
        </form>

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
    <?php
}

add_action('admin_init', 'popup_settings_init');

function popup_settings_init() {
    register_setting('popup_settings_group', 'popup_image_url');
    register_setting('popup_settings_group', 'popup_link_url');
    register_setting('popup_settings_group', 'popup_cookie_days_to_expire');
    register_setting('popup_settings_group', 'popup_cookie_name');
    register_setting('popup_settings_group', 'popup_enable');
}

function wgc_render_image_popup()
{
    if ( ! is_front_page() || get_option('popup_enable', "yes") == "no") {
        return;
    }

    $image_url = get_option('popup_image_url', ''); // ใส่ URL รูป Pop-up
    $link_url = get_option('popup_link_url', '#'); // กดที่รูปแล้วให้ไปไหน (ถ้าไม่ไปไหนใส่ #)
    $cookie_name = get_option('popup_cookie_name', 'popup_expire_in');
    $days_to_expire = get_option('popup_cookie_days_to_expire', 7);
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
            max-width: 90%;
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
            top: -15px;
            right: -15px;
            width: 35px;
            height: 35px;
            background: #fff;
            border-radius: 50%;
            color: #333;
            font-size: 18px;
            font-weight: bold;
            line-height: 30px;
            cursor: pointer;
            border: 2px solid #333;
            transition: 0.3s;
        }

        #wgc-popup-close:hover {
            background: #d63384;
            color: #fff;
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
                setCookie(popupName, "closed", expireDays);
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