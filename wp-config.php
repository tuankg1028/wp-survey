<?php
/**
 * Cấu hình cơ bản cho WordPress
 *
 * Trong quá trình cài đặt, file "wp-config.php" sẽ được tạo dựa trên nội dung
 * mẫu của file này. Bạn không bắt buộc phải sử dụng giao diện web để cài đặt,
 * chỉ cần lưu file này lại với tên "wp-config.php" và điền các thông tin cần thiết.
 *
 * File này chứa các thiết lập sau:
 *
 * * Thiết lập MySQL
 * * Các khóa bí mật
 * * Tiền tố cho các bảng database
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** Thiết lập MySQL - Bạn có thể lấy các thông tin này từ host/server ** //
/** Tên database MySQL */
define( 'DB_NAME', 'wp_survey' );

/** Username của database */
define( 'DB_USER', 'root' );

/** Mật khẩu của database */
define( 'DB_PASSWORD', '' );

/** Hostname của database */
define( 'DB_HOST', 'localhost' );

/** Database charset sử dụng để tạo bảng database. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Kiểu database collate. Đừng thay đổi nếu không hiểu rõ. */
define('DB_COLLATE', '');

/**#@+
 * Khóa xác thực và salt.
 *
 * Thay đổi các giá trị dưới đây thành các khóa không trùng nhau!
 * Bạn có thể tạo ra các khóa này bằng công cụ
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * Bạn có thể thay đổi chúng bất cứ lúc nào để vô hiệu hóa tất cả
 * các cookie hiện có. Điều này sẽ buộc tất cả người dùng phải đăng nhập lại.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ']aP0]Th!F#.5y89md2pPf4vx,%{;Ka5sY@at[*}{;AAeJ*Z&hQ5JIC}eE%X4hs25' );
define( 'SECURE_AUTH_KEY',  '^p_6}gJS>Wz.Y,(Y~y(-##S3T/MVKYYu}n&to^)#0;o*?_!08z6zmz33YeE|[R>h' );
define( 'LOGGED_IN_KEY',    'f8eh -<~xpjwAgI1=XvlS#J8=$U.AX8J+s6{rR*r.CW@gv]R(s?tAaRj037]7js,' );
define( 'NONCE_KEY',        'q-@FLpHfOnJzqC^A!ZtvJ[K:]A]Mt^C[Y=bu}]epSs4?9Vga#y>s!oUe4x`8M,$(' );
define( 'AUTH_SALT',        '<r&yhPPHm1LPHvDKk-*gej/sy)#XWPsa@| pt7]_HJ{#ORb>=m}j#hBE*u4|94on' );
define( 'SECURE_AUTH_SALT', 'Q)J8=dG*8KM5:**AC9;vYZZ_)1!!]-B#U#I@a~so,3L>M8uVu:ztdZ7nzBO3SpCE' );
define( 'LOGGED_IN_SALT',   '!:`,r=^kA :=y%Zg2Ew}Ujd2sC&=nAtHGD6I=0gZs:FOb!s}i)j{F#6`yW)w3=]y' );
define( 'NONCE_SALT',       ' 9<3?b:qfP1B,.Zet7ipdN!GXedk3#9B/kBLHV$jj@p0cAM-wNSk[aFzsR1to>:^' );

/**#@-*/

/**
 * Tiền tố cho bảng database.
 *
 * Đặt tiền tố cho bảng giúp bạn có thể cài nhiều site WordPress vào cùng một database.
 * Chỉ sử dụng số, ký tự và dấu gạch dưới!
 */
$table_prefix = 'wp_';

/**
 * Dành cho developer: Chế độ debug.
 *
 * Thay đổi hằng số này thành true sẽ làm hiện lên các thông báo trong quá trình phát triển.
 * Chúng tôi khuyến cáo các developer sử dụng WP_DEBUG trong quá trình phát triển plugin và theme.
 *
 * Để có thông tin về các hằng số khác có thể sử dụng khi debug, hãy xem tại Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* Đó là tất cả thiết lập, ngưng sửa từ phần này trở xuống. Chúc bạn viết blog vui vẻ. */

/** Đường dẫn tuyệt đối đến thư mục cài đặt WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Thiết lập biến và include file. */
require_once(ABSPATH . 'wp-settings.php');
