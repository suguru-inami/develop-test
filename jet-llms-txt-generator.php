<?php
/**
 * Plugin Name:       Jet LLMs.txt Generator (Minimal)
 * Plugin URI:        https://example.com/jet-llms-txt-generator (仮)
 * Description:       Generates llms.txt related files for LLMs. (Minimal version for rebuilding)
 * Version:           0.0.2
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Your Name or Company (仮)
 * Author URI:        https://example.com/ (仮)
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       jet-llms-txt-generator
 * Domain Path:       /languages
 */

// =========================================================================
// 定数定義
// =========================================================================
// このプラグインのバージョン。ヘッダーコメントの Version と合わせるのが一般的。
define( 'JET_LLMS_TXT_VERSION', '0.0.2' );
// プラグインのディレクトリパス（サーバー上の絶対パス）
define( 'JET_LLMS_TXT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
// プラグインのディレクトリURL
define( 'JET_LLMS_TXT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// =========================================================================
// セキュリティ
// =========================================================================
// PHPファイルへの直接アクセスを禁止します。
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// =========================================================================
// 管理画面メニューの追加
// =========================================================================

/**
 * 管理画面にプラグイン設定メニューを追加する関数
 */
function jet_llms_txt_admin_menu() {
    add_options_page(
        'LLMs.txt Generator Settings', // ページの<title>タグに出力されるテキスト
        'LLMs.txt Generator',        // メニューに表示されるテキスト
        'manage_options',            // このメニューを操作するために必要な権限
        'jet-llms-txt-settings',     // このメニューページのスラッグ（URLの一部になるユニークな名前）
        'jet_llms_txt_settings_page_html' // メニューページの内容を出力する関数名
    );
}
// 'admin_menu' アクションフックに関数を登録
add_action( 'admin_menu', 'jet_llms_txt_admin_menu' );


// =========================================================================
// 設定ページのHTML出力
// =========================================================================

/**
 * 設定ページのHTMLコンテンツを出力する関数
 * (手動生成ボタンと処理を追加)
 */
function jet_llms_txt_settings_page_html() {
    // 権限チェック
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // --- 手動生成ボタンが押された場合の処理 ---
    $generation_message = ''; // 生成結果メッセージ用変数
    if ( isset( $_POST['action'] ) && $_POST['action'] === 'generate_llms_txt' ) {
        // Nonce検証 (セキュリティ対策)
        if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'jet_llms_manual_generate_nonce' ) ) {
            // ファイル生成関数を呼び出す
            $result = jet_llms_txt_generate_llms_txt_file();
            if ( $result === true ) {
                $generation_message = '<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"><p><strong>llms.txt ファイルを生成しました。</strong></p></div>';
            } else {
                // エラーメッセージがあれば表示
                $error_msg = is_string( $result ) ? $result : 'llms.txt ファイルの生成に失敗しました。';
                $generation_message = '<div id="setting-error-settings_error" class="notice notice-error settings-error is-dismissible"><p><strong>' . esc_html( $error_msg ) . '</strong></p></div>';
            }
        } else {
            // Nonce検証失敗
            $generation_message = '<div id="setting-error-settings_error" class="notice notice-error settings-error is-dismissible"><p><strong>セキュリティチェックに失敗しました。ページを再読み込みしてもう一度お試しください。</strong></p></div>';
        }
    }
    // --- 手動生成処理ここまで ---


    // 設定保存時のメッセージ表示
    if ( isset( $_GET['settings-updated'] ) ) {
        add_settings_error( 'jet_llms_txt_messages', 'jet_llms_txt_message', '設定を保存しました。', 'updated' );
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <?php
        // 生成結果メッセージがあれば表示
        echo $generation_message;
        // 設定保存時のメッセージ表示
        settings_errors( 'jet_llms_txt_messages' );
        ?>

        <hr>
        <h2>手動生成</h2>
        <p>現在の設定に基づいて llms.txt ファイルを即時に生成・更新します。</p>
        <form method="post" action="">
            <?php // アクションとNonceフィールドを追加 ?>
            <input type="hidden" name="action" value="generate_llms_txt">
            <?php wp_nonce_field( 'jet_llms_manual_generate_nonce' ); ?>
            <?php submit_button( 'llms.txt を今すぐ生成', 'primary', 'submit_generate', false ); // ボタンのテキスト、タイプ、name、wrapしない ?>
         </form>
         <hr>


        <h2>設定</h2>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'jet_llms_txt_options_group' );
            do_settings_sections( 'jet-llms-txt-settings' );
            submit_button( '設定を保存' );
            ?>
        </form>
    </div>
    <?php
}


// =========================================================================
// Settings API の初期化とコールバック関数
// =========================================================================

/**
 * Settings API を初期化する関数
 */
function jet_llms_txt_settings_init() {
    // 1. 設定オプション名を登録
    register_setting(
        'jet_llms_txt_options_group',     // 設定グループ名
        'jet_llms_txt_options',         // オプション名 (DB保存キー)
        'jet_llms_txt_options_sanitize' // サニタイズコールバック関数
    );

    // --- 一般設定セクション ---
    add_settings_section(
        'jet_llms_txt_section_general',      // セクションID
        '一般設定',                         // セクションタイトル
        'jet_llms_txt_section_general_cb', // セクション説明コールバック
        'jet-llms-txt-settings'              // 表示ページスラッグ
    );

    // 対象投稿タイプフィールド
    add_settings_field(
        'jet_llms_txt_field_post_types', // フィールドID
        '対象投稿タイプ',                 // ラベル
        'jet_llms_txt_field_post_types_cb',// HTML出力コールバック
        'jet-llms-txt-settings',           // 表示ページスラッグ
        'jet_llms_txt_section_general',  // 所属セクションID
        [ 'label_for' => 'jet_llms_post_types' ] // label for 属性
    );

    // --- ルール設定セクション ---
    add_settings_section(
        'jet_llms_txt_section_rules',      // セクションID
        'ルール設定',                     // セクションタイトル
        'jet_llms_txt_section_rules_cb', // セクション説明コールバック
        'jet-llms-txt-settings'          // 表示ページスラッグ
    );

    // ユーザーエージェントルールフィールド
    add_settings_field(
        'jet_llms_txt_field_user_agent', // フィールドID
        'ユーザーエージェントルール<br>(User-agent / Allow / Disallow)', // ラベル
        'jet_llms_txt_field_user_agent_cb', // HTML出力コールバック
        'jet-llms-txt-settings',           // 表示ページスラッグ
        'jet_llms_txt_section_rules',  // 所属セクションID
        [ 'label_for' => 'jet_llms_user_agent_rules' ] // label for 属性
    );

    // カスタムルールフィールド
    add_settings_field(
        'jet_llms_txt_field_custom_rules', // フィールドID
        'カスタムルール<br>(Training / Scraping / APIAccess etc.)', // ラベル
        'jet_llms_txt_field_custom_rules_cb', // HTML出力コールバック
        'jet-llms-txt-settings',           // 表示ページスラッグ
        'jet_llms_txt_section_rules', // 所属セクションID
        [ 'label_for' => 'jet_llms_custom_rules' ] // label for 属性
    );

    // 手動追加URLフィールド
    add_settings_field(
        'jet_llms_txt_field_manual_urls', // フィールドID
        '手動で追加するURL',              // ラベル
        'jet_llms_txt_field_manual_urls_cb', // HTML出力コールバック
        'jet-llms-txt-settings',           // 表示ページスラッグ
        'jet_llms_txt_section_rules', // 所属セクションID
        [ 'label_for' => 'jet_llms_manual_urls' ] // label for 属性
    );

     // --- .md ページ生成設定セクション ---
    add_settings_section(
        'jet_llms_txt_section_md',         // セクションID
        '.md ページ生成設定',              // セクションタイトル
        'jet_llms_txt_section_md_cb',    // セクション説明コールバック
        'jet-llms-txt-settings'           // 表示ページスラッグ
    );

    // .md生成の有効化フィールド
    add_settings_field(
        'jet_llms_txt_field_enable_md',    // フィールドID
        '.md ファイル生成',                 // ラベル
        'jet_llms_txt_field_enable_md_cb', // HTML出力コールバック
        'jet-llms-txt-settings',            // 表示ページスラッグ
        'jet_llms_txt_section_md'       // 所属セクションID
    );

    // 含めるメタ情報フィールド
    add_settings_field(
        'jet_llms_txt_field_meta_in_md',  // フィールドID
        '含めるメタ情報',                  // ラベル
        'jet_llms_txt_field_meta_in_md_cb',// HTML出力コールバック
        'jet-llms-txt-settings',            // 表示ページスラッグ
        'jet_llms_txt_section_md',      // 所属セクションID
        [ 'label_for' => 'jet_llms_meta_in_md' ] // fieldset に対応させる
    );

    // --- ここに他の設定フィールドも同様に追加していきます ---
}
// 'admin_init' アクションフックで上記の初期化関数を呼び出す
add_action( 'admin_init', 'jet_llms_txt_settings_init' );


// --- コールバック関数群 ---

/**
 * '一般設定' セクションのコールバック関数
 */
function jet_llms_txt_section_general_cb( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>">llms.txt に含める基本的なコンテンツ設定を行います。</p>
    <?php
}

/**
 * '対象投稿タイプ' フィールドのコールバック関数 (チェックボックス)
 */
function jet_llms_txt_field_post_types_cb( $args ) {
    $options = get_option( 'jet_llms_txt_options' );
    $selected_post_types = isset( $options['post_types'] ) ? (array) $options['post_types'] : [];
    $post_types = get_post_types( [ 'public' => true ], 'objects' );

    $html = '<fieldset id="jet_llms_post_types">';
    foreach ( $post_types as $post_type ) {
        if ( $post_type->name === 'attachment' ) {
            continue;
        }
        $checkbox_id = 'post_type_' . esc_attr( $post_type->name );
        $html .= sprintf(
            '<label for="%s" style="margin-right: 15px;"><input type="checkbox" id="%s" name="jet_llms_txt_options[post_types][]" value="%s" %s> %s (%s)</label><br>',
            $checkbox_id,
            $checkbox_id,
            esc_attr( $post_type->name ),
            checked( in_array( $post_type->name, $selected_post_types, true ), true, false ),
            esc_html( $post_type->label ),
            esc_html( $post_type->name )
        );
    }
    $html .= '</fieldset>';
    $html .= '<p class="description">llms.txt のコンテンツリストに含める投稿タイプを選択してください。</p>';

    echo $html;
}

/**
 * 'ルール設定' セクションのコールバック関数
 */
function jet_llms_txt_section_rules_cb( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>">llms.txt に出力するアクセスルールや追加URLを設定します。</p>
    <?php
}

/**
 * 'ユーザーエージェントルール' フィールドのコールバック関数 (テキストエリア)
 */
function jet_llms_txt_field_user_agent_cb( $args ) {
    $options = get_option( 'jet_llms_txt_options' );
    $value = isset( $options['user_agent_rules'] ) ? $options['user_agent_rules'] : "User-agent: *\nAllow: /"; // デフォルト値の例
    ?>
    <textarea id="jet_llms_user_agent_rules" name="jet_llms_txt_options[user_agent_rules]" rows="5" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
    <p class="description">例:<br><code>User-agent: *<br>Allow: /wp-content/uploads/<br>Disallow: /private/</code></p>
    <?php
}

/**
 * 'カスタムルール' フィールドのコールバック関数 (テキストエリア)
 */
function jet_llms_txt_field_custom_rules_cb( $args ) {
    $options = get_option( 'jet_llms_txt_options' );
    $value = isset( $options['custom_rules'] ) ? $options['custom_rules'] : '';
    ?>
    <textarea id="jet_llms_custom_rules" name="jet_llms_txt_options[custom_rules]" rows="5" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
    <p class="description">例:<br><code># Training: allowed<br># Scraping: limited<br># APIAccess: restricted</code><br><code>x-content-license: "(c) <?php echo date('Y'); ?> YourSite. All rights reserved."</code><br><code>x-ai-training-policy: "disallowed"</code> など、自由記述。</p>
    <?php
}

/**
 * '手動追加URL' フィールドのコールバック関数 (テキストエリア)
 */
function jet_llms_txt_field_manual_urls_cb( $args ) {
    $options = get_option( 'jet_llms_txt_options' );
    $value = isset( $options['manual_urls'] ) ? $options['manual_urls'] : '';
    ?>
    <textarea id="jet_llms_manual_urls" name="jet_llms_txt_options[manual_urls]" rows="5" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
    <p class="description">llms.txt のコンテンツリストに、投稿タイプ以外で手動で追加したいURLがあれば1行に1つずつ入力してください。(例: <code>https://example.com/important-page.html</code>)</p>
    <?php
}

/**
 * '.md ページ生成設定' セクションのコールバック関数
 */
function jet_llms_txt_section_md_cb( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>">各投稿に対応する .md ファイルの自動生成に関する設定を行います。</p>
    <?php
}

/**
 * '.md生成の有効化' フィールドのコールバック関数 (チェックボックス)
 */
function jet_llms_txt_field_enable_md_cb( $args ) {
    $options = get_option( 'jet_llms_txt_options' );
    $checked = isset( $options['enable_md_generation'] ) && $options['enable_md_generation'] === 'yes';
    ?>
    <label>
        <input type="checkbox" name="jet_llms_txt_options[enable_md_generation]" value="yes" <?php checked( $checked, true ); ?> >
        投稿・固定ページの保存時に .md ファイルを自動生成する
    </label>
    <p class="description">有効にすると、対象投稿タイプのコンテンツからMarkdownファイルを生成し、サーバー内に保存します。(保存先は別途定義)</p>
     <?php
}

/**
 * '含めるメタ情報' フィールドのコールバック関数 (チェックボックス群)
 */
function jet_llms_txt_field_meta_in_md_cb( $args ) {
    $options = get_option( 'jet_llms_txt_options' );
    $selected_meta = isset( $options['meta_in_md'] ) ? (array) $options['meta_in_md'] : [];
    $available_meta = [
        'title'        => 'タイトル (Title)',
        'publish_date' => '公開日 (Date)',
        'author'       => '著者 (Author)',
    ];

    $html = '<fieldset id="jet_llms_meta_in_md">';
    foreach ( $available_meta as $key => $label ) {
         $checkbox_id = 'meta_' . esc_attr( $key );
         $html .= sprintf(
             '<label for="%s" style="margin-right: 15px;"><input type="checkbox" id="%s" name="jet_llms_txt_options[meta_in_md][]" value="%s" %s> %s</label><br>',
             $checkbox_id,
             $checkbox_id,
             esc_attr( $key ),
             checked( in_array( $key, $selected_meta, true ), true, false ),
             esc_html( $label )
         );
    }
     $html .= '</fieldset>';
     $html .= '<p class="description">生成される .md ファイルの先頭に含めるメタ情報（YAML Front Matter形式を想定）を選択してください。</p>';

    echo $html;
}


// =========================================================================
// 設定値のサニタイズ（無害化）
// =========================================================================

/**
 * 設定値のサニタイズコールバック関数 (推奨)
 */
function jet_llms_txt_options_sanitize( $input ) {
    $sanitized_input = []; // サニタイズ後の値を格納する配列

    // 'post_types' フィールドの値が存在すればサニタイズ
    if ( isset( $input['post_types'] ) ) {
        $sanitized_input['post_types'] = is_array( $input['post_types'] )
            ? array_map( 'sanitize_key', $input['post_types'] )
            : [];
    } else {
        $sanitized_input['post_types'] = [];
    }

    // user_agent_rules フィールドの値をサニタイズ
    if ( isset( $input['user_agent_rules'] ) ) {
        $sanitized_input['user_agent_rules'] = sanitize_textarea_field( $input['user_agent_rules'] );
    } else {
        $sanitized_input['user_agent_rules'] = '';
    }

    // custom_rules フィールドの値をサニタイズ
    if ( isset( $input['custom_rules'] ) ) {
        $sanitized_input['custom_rules'] = sanitize_textarea_field( $input['custom_rules'] );
    } else {
        $sanitized_input['custom_rules'] = '';
    }

    // manual_urls フィールドの値をサニタイズ
    if ( isset( $input['manual_urls'] ) ) {
         $lines = explode( "\n", $input['manual_urls'] );
         $sanitized_urls = [];
         foreach( $lines as $line ) {
             $trimmed_line = trim( $line );
             if ( ! empty( $trimmed_line ) ) {
                 $sanitized_urls[] = esc_url_raw( $trimmed_line );
             }
         }
         $sanitized_input['manual_urls'] = implode( "\n", $sanitized_urls );
    } else {
        $sanitized_input['manual_urls'] = '';
    }

    // enable_md_generation フィールドの値をサニタイズ
    if ( isset( $input['enable_md_generation'] ) && $input['enable_md_generation'] === 'yes' ) {
        $sanitized_input['enable_md_generation'] = 'yes';
    } else {
         $sanitized_input['enable_md_generation'] = '';
    }

    // meta_in_md フィールドの値をサニタイズ
    if ( isset( $input['meta_in_md'] ) ) {
        $allowed_meta_keys = ['title', 'publish_date', 'author'];
        $sanitized_meta = [];
        if ( is_array( $input['meta_in_md'] ) ) {
            foreach( $input['meta_in_md'] as $meta_key ) {
                $sanitized_key = sanitize_key( $meta_key );
                if ( in_array( $sanitized_key, $allowed_meta_keys, true ) ) {
                    $sanitized_meta[] = $sanitized_key;
                }
            }
        }
         $sanitized_input['meta_in_md'] = $sanitized_meta;
    } else {
        $sanitized_input['meta_in_md'] = [];
    }

    // --- ここに他の設定値のサニタイズ処理を追加していきます ---

    return $sanitized_input; // サニタイズ後の配列を返す
}


// =========================================================================
// llms.txt ファイル生成処理
// =========================================================================

/**
 * llms.txt ファイルを生成・更新する関数
 *
 * @return bool|string 成功時は true、失敗時はエラーメッセージ文字列
 */
function jet_llms_txt_generate_llms_txt_file() {
    $options = get_option( 'jet_llms_txt_options', [] );
    $llms_content = ''; // ファイルの内容をここに組み立てる

    // --- 1. ルール部分の生成 ---
    $llms_content .= "# LLMs.txt Generated by Jet LLMs.txt Generator v" . JET_LLMS_TXT_VERSION . "\n";
    $llms_content .= "# Generated at: " . current_time('mysql') . "\n\n";

    // ユーザーエージェントルール
    if ( ! empty( $options['user_agent_rules'] ) ) {
        $llms_content .= "## User Agent Rules\n";
        $llms_content .= trim( $options['user_agent_rules'] ) . "\n\n";
    }

    // カスタムルール
    if ( ! empty( $options['custom_rules'] ) ) {
        $llms_content .= "## Custom Rules\n";
        $llms_content .= trim( $options['custom_rules'] ) . "\n\n";
    }

    // --- 2. コンテンツリスト部分の生成 ---
    $llms_content .= "## Content Index\n";

    // 対象投稿タイプの取得
    $post_types = ! empty( $options['post_types'] ) ? $options['post_types'] : [];

    if ( ! empty( $post_types ) ) {
        $args = [
            'post_type' => $post_types,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                global $post;
                $link_url = get_permalink( $post->ID );
                $post_title = get_the_title( $post->ID );
                $llms_content .= "- [" . esc_html( $post_title ) . "](" . esc_url( $link_url ) . ")\n";
            }
            wp_reset_postdata();
        } else {
            $llms_content .= "No posts found for the selected post types.\n";
        }
    } else {
        $llms_content .= "No post types selected in settings.\n";
    }
    $llms_content .= "\n";

    // 手動追加URL
    if ( ! empty( $options['manual_urls'] ) ) {
        $llms_content .= "## Manual URLs\n";
        $manual_urls = explode( "\n", trim( $options['manual_urls'] ) );
        foreach ( $manual_urls as $url_line ) {
            $url_line = trim( $url_line );
            if ( ! empty( $url_line ) ) {
                $llms_content .= "- [" . esc_url( $url_line ) . "](" . esc_url( $url_line ) . ")\n";
            }
        }
        $llms_content .= "\n";
    }

    // --- 3. ファイルへの書き込み ---
    $home_path = get_home_path();
    $llms_txt_path = $home_path . 'llms.txt';
    $write_result = @file_put_contents( $llms_txt_path, $llms_content );

    if ( $write_result === false ) {
         error_log( '[Jet LLMs.txt Generator] Failed to write llms.txt file. Check file permissions for ' . $llms_txt_path );
        return 'llms.txt ファイルの書き込みに失敗しました。サーバーのルートディレクトリ (' . esc_html($home_path) . ') に書き込み権限があるか確認してください。';
    }

    return true; // 成功
}

// --- ここに他のプラグイン機能関数を追加 ---

?>