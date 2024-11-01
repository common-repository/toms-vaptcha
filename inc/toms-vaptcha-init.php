<?php
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('TomSVaptchaFrontend') ){

    class TomSVaptchaFrontend {

        /**
        *  TomS Foreach Function
        * @param $array                    Array which need to foreach.
        * @param $wp_options_option_name   Wordpress Database Talbe 'wp_options' -> 'option_name' name.
        * @param $default                  If the option_name is undefined then use this default value
        * @return $value                   the value of the match key of the array.
        */
        function toms_foreach($array, $wp_options_option_name, $default){
            $default_value = !empty($default) ? $default : '';
            foreach ($array as $key => $value) {
                if ( $key == get_option( $wp_options_option_name, $default_value ) ){
                    return $value;
                }
            }
        }

        /**
         * TomS Vaptcha JS
         * @version v3
        */
        function TomSVaptcha_Frontend_JS(){
            $vaptcha_js = !empty( esc_url( get_option('toms_vaptcha_online_url' ) ) ) ? esc_url( get_option('toms_vaptcha_online_url' ) ) : plugin_dir_url( __FILE__ ) . 'assets/js/v3.js';
            
            $html = [
                'src'   => esc_textarea( $vaptcha_js )
            ];
            return $html;
        }

        /**
         * TomS Vaptcha Click Mode Frontend HTML
         * @param $form The Form id or class name
         */
        function TomSVaptcha_Click_Mode_HTML($form){
            $vaptcha_form = $form;
            if( preg_match('/^\./', $vaptcha_form) != 0 ){ //check the name of form id or form class
                $sign               = '.';
                $id_class           = 'class';
                $suffix             = '-toms-vaptcha';
                $vaptcha_form_name  = ltrim($vaptcha_form, '.'); //Delete the first Ending mark'.'
            }else{
                $sign               = '#';
                $id_class           = 'id';
                $suffix             = '-toms-vaptcha';
                $vaptcha_form_name  = $vaptcha_form;
            }
            $html = '';
            ob_start(); ?>
            <style>
                <?php echo esc_html( $sign.$vaptcha_form_name.$suffix ); ?>{
                    width: 100%;
                    min-height: 36px;
                    margin-bottom: 16px;
                }
                .vaptcha-init-main {
                    display: table;
                    width: 100%;
                    height: 100%;
                    background-color: #eeeeee;
                }
                .vaptcha-init-loading {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .vaptcha-init-loading img {
                    width: 24px;
                    padding-right: 10px;
                }
                .vaptcha-init-loading .vaptcha-text {
                    font-family: sans-serif;
                    font-size: 12px;
                    color: #cccccc;
                    vertical-align: middle;
                }
            </style>
            <div <?php echo esc_attr( $id_class ) . '="' . esc_attr( $vaptcha_form_name.$suffix ) . '"';?> >
                <div class="vaptcha-init-main">
                    <div class="vaptcha-init-loading">
                        <img src="data:image/svg+xml;base64,PHN2ZyAgICB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHdpZHRoPSI0OHB4IgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGhlaWdodD0iNjBweCIKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB2aWV3Qm94PSIwIDAgMjQgMzAiCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOiBuZXcgMCAwIDUwIDUwOyB3aWR0aDogMTRweDsgaGVpZ2h0OiAxNHB4OyB2ZXJ0aWNhbC1hbGlnbjogbWlkZGxlIgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHhtbDpzcGFjZT0icHJlc2VydmUiCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8cmVjdCB4PSIwIiB5PSI5LjIyNjU2IiB3aWR0aD0iNCIgaGVpZ2h0PSIxMi41NDY5IiBmaWxsPSIjQ0NDQ0NDIj4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8YW5pbWF0ZSBhdHRyaWJ1dGVOYW1lPSJoZWlnaHQiIGF0dHJpYnV0ZVR5cGU9IlhNTCIgdmFsdWVzPSI1OzIxOzUiIGJlZ2luPSIwcyIgZHVyPSIwLjZzIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSI+PC9hbmltYXRlPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgPGFuaW1hdGUgYXR0cmlidXRlTmFtZT0ieSIgYXR0cmlidXRlVHlwZT0iWE1MIiB2YWx1ZXM9IjEzOyA1OyAxMyIgYmVnaW49IjBzIiBkdXI9IjAuNnMiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIj48L2FuaW1hdGU+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3JlY3Q+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8cmVjdCB4PSIxMCIgeT0iNS4yMjY1NiIgd2lkdGg9IjQiIGhlaWdodD0iMjAuNTQ2OSIgZmlsbD0iI0NDQ0NDQyI+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGFuaW1hdGUgYXR0cmlidXRlTmFtZT0iaGVpZ2h0IiBhdHRyaWJ1dGVUeXBlPSJYTUwiIHZhbHVlcz0iNTsyMTs1IiBiZWdpbj0iMC4xNXMiIGR1cj0iMC42cyIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiPjwvYW5pbWF0ZT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxhbmltYXRlIGF0dHJpYnV0ZU5hbWU9InkiIGF0dHJpYnV0ZVR5cGU9IlhNTCIgdmFsdWVzPSIxMzsgNTsgMTMiIGJlZ2luPSIwLjE1cyIgZHVyPSIwLjZzIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSI+PC9hbmltYXRlPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9yZWN0PgogICAgICAgICAgICAgICAgICAgICAgICAgICAgPHJlY3QgeD0iMjAiIHk9IjguNzczNDQiIHdpZHRoPSI0IiBoZWlnaHQ9IjEzLjQ1MzEiIGZpbGw9IiNDQ0NDQ0MiPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxhbmltYXRlIGF0dHJpYnV0ZU5hbWU9ImhlaWdodCIgYXR0cmlidXRlVHlwZT0iWE1MIiB2YWx1ZXM9IjU7MjE7NSIgYmVnaW49IjAuM3MiIGR1cj0iMC42cyIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiPjwvYW5pbWF0ZT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxhbmltYXRlIGF0dHJpYnV0ZU5hbWU9InkiIGF0dHJpYnV0ZVR5cGU9IlhNTCIgdmFsdWVzPSIxMzsgNTsgMTMiIGJlZ2luPSIwLjNzIiBkdXI9IjAuNnMiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIj48L2FuaW1hdGU+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3JlY3Q+CiAgICAgICAgICAgICAgICAgICAgICAgIDwvc3ZnPg==" />
                        <span class="vaptcha-text"><?php _e('Vaptcha Initializing...', 'toms-vaptcha'); ?></span>
                    </div>
                </div>
            </div>
            <?php $html = ob_get_contents();
                ob_end_clean();
                return $html ;
            }
            

        /**
         * TomS Vaptcha Click Mode Frontend JS
         * 
         * Wordpress Default Form click mode Frontend JS.
         * 
         * @param $form The Form id or class name
         * 
        */
        function TomSVaptcha_Click_Mode_JS($form){
            if( !empty($form) ){
                $lang_value = ['auto', 'en', 'zh-CN', 'zh-TW', 'jp'];
                $area_value = ['auto', 'sea', 'na', 'cn'];

                $vid            = esc_textarea( get_option('toms_vaptcha_vid') );
                $https          = esc_textarea( get_option('toms_vaptcha_https', "0") ) == "0" ? 'true' : 'false';
                $lang           = esc_textarea( $this->toms_foreach( $lang_value, 'toms_vaptcha_language', "0" ) );
                $area           = esc_textarea( $this->toms_foreach( $area_value, 'toms_vaptcha_area', "0" ) );
                $button_style   = esc_textarea( get_option('toms_vaptcha_button_style', "0") ) == "0" ? 'dark' : 'light';
                $button_color   = !empty( esc_textarea( get_option('toms_vaptcha_button_color') )) ? esc_textarea( get_option('toms_vaptcha_button_color', '#57ABFF') ) : '#57ABFF';
                $guide          = esc_textarea( get_option('toms_vaptcha_guide', "0") ) == "0" ? 'true' : 'false';
                $vaptcha_form   = esc_attr( $form );

                if( preg_match('/^\./', $vaptcha_form) != 0 ){ //check the name of form id or form class
                    $sign               = '.';
                    $id_class           = 'class';   
                    $suffix             = '-toms-vaptcha';
                    $vaptcha_form_name  = ltrim($vaptcha_form, '.'); //Delete the first Ending mark'.'
                }else{
                    $sign               = '#';
                    $id_class           = 'id';
                    $suffix             = '-toms-vaptcha';
                    $vaptcha_form_name  = $vaptcha_form;
                }
                
                $html = '';
                ob_start() ?>
                    vaptcha({
                        vid:            '<?php echo esc_textarea($vid); ?>',
                        mode:           'click',
                        scene:          0, 
                        container:      '<?php echo esc_textarea($sign.$vaptcha_form_name.$suffix); ?>',
                        style:          '<?php echo esc_textarea($button_style); ?>',
                        color:          '<?php echo esc_textarea($button_color); ?>',
                        lang:           '<?php echo esc_textarea($lang); ?>',
                        https:          '<?php echo esc_textarea($https); ?>',
                        area:           '<?php echo esc_textarea($area); ?>',
                        guide:          '<?php echo esc_textarea($guide); ?>'
                    }).then(
                        function( vaptchaObj ){                   
                            Obj = vaptchaObj;
                            Obj.render();
                            Obj.renderTokenInput("<?php echo esc_textarea($vaptcha_form.$suffix); ?>");
                        }
                    )
                <?php
                $html = ob_get_contents();
                ob_end_clean();

                return $html ;
            }
        }

        /**
         * TomS Vaptcha Embedded Mode Frontend HTML
         * @param $form The Form id or class name
         */
        function TomSVaptcha_Embedded_Mode_HTML($form){
            $vaptcha_form = $form;
            if( preg_match('/^\./', $vaptcha_form) != 0 ){ //check the name of form id or form class
                $sign               = '.';
                $id_class           = 'class';
                $suffix             = '-toms-vaptcha';
                $vaptcha_form_name  = ltrim($vaptcha_form, '.'); //Delete the first Ending mark'.'
            }else{
                $sign               = '#';
                $id_class           = 'id';
                $suffix             = '-toms-vaptcha';
                $vaptcha_form_name  = $vaptcha_form;
            }
            $html = '';
            ob_start(); ?>
                <style>
                    <?php echo esc_attr( $sign.$vaptcha_form_name.$suffix );?>{
                        width: 400px;
                        height: 230px;
                    }
                    @media (max-width: 500px){
                        <?php echo esc_attr( $sign.$vaptcha_form_name.$suffix );?>{
                            width: 100%;
                            height: auto;
                        }
                    }
                </style>
                <div <?php echo esc_attr( $id_class ) . '="' . esc_attr( $vaptcha_form_name.$suffix ) . '"';?> ></div>
            <?php
            $html = ob_get_contents();
            ob_end_clean();

            return $html ;
        }

        /**
         * TomS Vaptcha Embedded Mode Frontend JS
         * 
         * Wordpress Default Form Embedded mode Frontend JS.
         * 
         * @param $form The Form id or class name
         * 
        */
        function TomSVaptcha_Embedded_Mode_JS($form){
            if( !empty($form) ){
                $lang_value = ['auto', 'en', 'zh-CN', 'zh-TW', 'jp'];
                $area_value = ['auto', 'sea', 'na', 'cn'];

                $vid            = esc_textarea( get_option('toms_vaptcha_vid') );
                $https          = esc_textarea( get_option('toms_vaptcha_https', "0") ) == "0" ? 'true' : 'false';
                $lang           = esc_textarea( $this->toms_foreach( $lang_value, 'toms_vaptcha_language', "0" ) );
                $area           = esc_textarea( $this->toms_foreach( $area_value, 'toms_vaptcha_area', "0" ) );
                $guide          = esc_textarea( get_option('toms_vaptcha_guide', "0") ) == "0" ? 'true' : 'false';
                $vaptcha_form   = esc_attr( $form );

                if( preg_match('/^\./', $vaptcha_form) != 0 ){ //check the name of form id or form class
                    $sign               = '.';
                    $id_class           = 'class';   
                    $suffix             = '-toms-vaptcha';
                    $vaptcha_form_name  = ltrim($vaptcha_form, '.'); //Delete the first Ending mark'.'
                }else{
                    $sign               = '#';
                    $id_class           = 'id';
                    $suffix             = '-toms-vaptcha';
                    $vaptcha_form_name  = $vaptcha_form;
                }
                
                $html = '';
                ob_start() ?>
                    vaptcha({
                        vid:            '<?php echo esc_textarea($vid); ?>',
                        mode:           'embedded',
                        scene:          0, 
                        container:      '<?php echo esc_textarea($sign.$vaptcha_form_name.$suffix); ?>',
                        lang:           '<?php echo esc_textarea($lang); ?>',
                        https:          '<?php echo esc_textarea($https); ?>',
                        area:           '<?php echo esc_textarea($area); ?>',
                        guide:          '<?php echo esc_textarea($guide); ?>'
                    }).then(
                        function( vaptchaObj ){                   
                            Obj = vaptchaObj;
                            Obj.render();
                            Obj.renderTokenInput("<?php echo esc_textarea($vaptcha_form.$suffix); ?>");
                        }
                    )
                <?php  
                $html = ob_get_contents();
                ob_end_clean();

                return $html ;
            }
        }

        /**
         * TomS Vaptcha Invisible Mode Frontend JS
         * 
         * Wordpress Default Form Invisible mode Frontend JS.
         * 
         * @param $form The Form id or class name
         * 
        */
        function TomSVaptcha_Invisible_Mode_JS($form){
            if( !empty($form) ){
                $lang_value = ['auto', 'en', 'zh-CN', 'zh-TW', 'jp'];
                $area_value = ['auto', 'sea', 'na', 'cn'];

                $vid            = esc_textarea( get_option('toms_vaptcha_vid') );
                $https          = esc_textarea( get_option('toms_vaptcha_https', "0") ) == "0" ? 'true' : 'false';
                $lang           = esc_textarea( $this->toms_foreach( $lang_value, 'toms_vaptcha_language', "0" ) );
                $area           = esc_textarea( $this->toms_foreach( $area_value, 'toms_vaptcha_area', "0" ) );
                $guide          = esc_textarea( get_option('toms_vaptcha_guide', "0") ) == "0" ? 'true' : 'false';
                $vaptcha_form   = esc_attr( $form );

                if( preg_match('/^\./', $vaptcha_form) != 0 ){ //check the name of form id or form class
                    $element            = 'getElementsByClassName';
                    $number             = '[0]';
                    $button_id          = 'submit';
                    $sign               = '.';
                    $id_class           = 'class';
                    $suffix             = '-toms-vaptcha';
                    $vaptcha_form_name  = ltrim($vaptcha_form, '.'); //Delete the first Ending mark'.'
                }else{
                    $element            = 'getElementById';
                    $number             = '';
                    $button_id          = 'wp-submit';
                    $sign               = '#';
                    $id_class           = 'id';
                    $suffix             = '-toms-vaptcha';
                    $vaptcha_form_name  = $vaptcha_form;
                }
                
                $html = '';
                ob_start(); ?>
                    var vaptcha_div = document.createElement("div");
                    vaptcha_div.setAttribute("<?php echo esc_attr( $id_class ); ?>", "<?php echo esc_attr( $vaptcha_form_name.$suffix ); ?>");
                    document.<?php echo esc_html($element); ?>("<?php echo esc_textarea($vaptcha_form_name); ?>")<?php echo esc_html($number)?>.appendChild(vaptcha_div);

                    vaptcha({
                        vid:            '<?php echo esc_textarea($vid); ?>',
                        mode:           'invisible',
                        scene:          0,
                        lang:           '<?php echo esc_textarea($lang); ?>',
                        https:          '<?php echo esc_textarea($https); ?>',
                        area:           '<?php echo esc_textarea($area); ?>',
                        guide:          '<?php echo esc_textarea($guide); ?>'
                    }).then(
                        function( vaptchaObj ){                   
                            Obj = vaptchaObj;
                            Obj.listen('pass', function () {
        
                                data = Obj.getServerToken();

                                //Invisile Mode, after passed, create 2 input tags that contain vaptcha_server and vaptcha_token to the form for submit to the server.
                                if(!!(data.server) && !!(data.token)) {
                                    var vaptcha_server = document.createElement("INPUT");
                                    vaptcha_server.setAttribute("type", "hidden");
                                    vaptcha_server.setAttribute("name", "vaptcha_server");
                                    vaptcha_server.setAttribute("id", "vaptcha_server");
                                    vaptcha_server.setAttribute("value", data.server);

                                    var vaptcha_token = document.createElement("INPUT");
                                    vaptcha_token.setAttribute("type", "hidden");
                                    vaptcha_token.setAttribute("name", "vaptcha_token");
                                    vaptcha_token.setAttribute("id", "vaptcha_token");
                                    vaptcha_token.setAttribute("value", data.token);

                                    document.<?php echo esc_html($element); ?>("<?php echo esc_textarea($vaptcha_form_name.$suffix); ?>")<?php echo esc_html($number)?>.appendChild(vaptcha_server);
                                    document.<?php echo esc_html($element); ?>("<?php echo esc_textarea($vaptcha_form_name.$suffix); ?>")<?php echo esc_html($number)?>.appendChild(vaptcha_token);

                                    setTimeout(function() {
                                        document.getElementById('vaptcha_server').remove();
                                        document.getElementById('vaptcha_token').remove();
                                        Obj.reset();
                                    }, 180000);
                                }
                            })

                        //add click event to whole forms for invisible mode
                        var TomSClick = document.<?php echo esc_html($element); ?>("<?php echo esc_html($vaptcha_form_name); ?>")<?php echo esc_html($number)?>;
                        
                        TomSClick.addEventListener('click', function() {
                            Obj.validate();
                        });

                        //If the Close button manually close, reset the verification.
                        Obj.listen('close', function () {
                            Obj.reset();
                        })
                        }
                    )
                <?php  
                $html = ob_get_contents();
                ob_end_clean();

                return $html;
            }
        }

        /**
         * TomS Vaptcha Verify Frontend Only Style
         * 
         *  @param $form            The Form id or class name
         *  @param $submit_class    The submit class name
         *  @param $nonce_name      The nonce name
         * 
        */
        function TomSVaptcha_Verify_Frontend_Only_style($form, $submit_class, $nonce_name){
            $lang_value = ['auto', 'en', 'zh-CN', 'zh-TW', 'jp'];
            $area_value = ['auto', 'sea', 'na', 'cn'];

            $vid            = esc_textarea( get_option('toms_vaptcha_vid') );
            $https          = esc_textarea( get_option('toms_vaptcha_https', "0") ) == "0" ? 'true' : 'false';
            $lang           = esc_textarea( $this->toms_foreach( $lang_value, 'toms_vaptcha_language', "0" ) );
            $area           = esc_textarea( $this->toms_foreach( $area_value, 'toms_vaptcha_area', "0" ) );
            $button_style   = esc_textarea( get_option('toms_vaptcha_button_style', "0") ) == "0" ? 'dark' : 'light';
            $button_color   = !empty( esc_textarea( get_option('toms_vaptcha_button_color') )) ? esc_textarea( get_option('toms_vaptcha_button_color', '#57ABFF') ) : '#57ABFF';
            $guide          = esc_textarea( get_option('toms_vaptcha_guide', "0") ) == "0" ? 'true' : 'false';
            $vaptcha_form   = esc_attr( $form );

            if( preg_match('/^\./', $vaptcha_form) != 0 ){ //check the name of form id or form class
                $element            = 'getElementsByClassName';
                $number             = '[0]';
                $sign               = '.';
                $id_class           = 'class';   
                $suffix             = '-toms-vaptcha';
                $vaptcha_form_name  = ltrim($vaptcha_form, '.'); //Delete the first Ending mark'.'
            }else{
                $element            = 'getElementById';
                $number             = '';
                $sign               = '#';
                $id_class           = 'id';
                $suffix             = '-toms-vaptcha';
                $vaptcha_form_name  = $vaptcha_form;
            }
            
            $html = '';
            ob_start() ?>
                <style>
                    .toms-vaptcha-not-allowed{
                        background-color: #918a8a !important;
                        cursor: not-allowed !important;
                    }
                    .toms-vaptcha-not-allowed:hover{
                        background-color: #ff0000 !important;
                    }
                    .toms-vaptcha-allowed:hover{
                        background-color: #009245 !important;
                    }
                </style>
            <?php  
            $html = ob_get_contents();
            ob_end_clean();

            return $html ;
        }
        /**
         * TomS Vaptcha Verify Frontend Only JS
         * 
         *  @param $form            The Form id or class name
         *  @param $submit_class    The submit class name
         *  @param $nonce_name      The nonce name
         * 
        */
        function TomSVaptcha_Verify_Frontend_Only_JS($form, $submit_class, $nonce_name){
            $lang_value = ['auto', 'en', 'zh-CN', 'zh-TW', 'jp'];
            $area_value = ['auto', 'sea', 'na', 'cn'];

            $vid            = esc_textarea( get_option('toms_vaptcha_vid') );
            $https          = esc_textarea( get_option('toms_vaptcha_https', "0") ) == "0" ? 'true' : 'false';
            $lang           = esc_textarea( $this->toms_foreach( $lang_value, 'toms_vaptcha_language', "0" ) );
            $area           = esc_textarea( $this->toms_foreach( $area_value, 'toms_vaptcha_area', "0" ) );
            $button_style   = esc_textarea( get_option('toms_vaptcha_button_style', "0") ) == "0" ? 'dark' : 'light';
            $button_color   = !empty( esc_textarea( get_option('toms_vaptcha_button_color') )) ? esc_textarea( get_option('toms_vaptcha_button_color', '#57ABFF') ) : '#57ABFF';
            $guide          = esc_textarea( get_option('toms_vaptcha_guide', "0") ) == "0" ? 'true' : 'false';
            $vaptcha_form   = esc_attr( $form );

            if( preg_match('/^\./', $vaptcha_form) != 0 ){ //check the name of form id or form class
                $element            = 'getElementsByClassName';
                $number             = '[0]';
                $sign               = '.';
                $id_class           = 'class';   
                $suffix             = '-toms-vaptcha';
                $vaptcha_form_name  = ltrim($vaptcha_form, '.'); //Delete the first Ending mark'.'
            }else{
                $element            = 'getElementById';
                $number             = '';
                $sign               = '#';
                $id_class           = 'id';
                $suffix             = '-toms-vaptcha';
                $vaptcha_form_name  = $vaptcha_form;
            }
            
            $html = '';
            ob_start() ?>

                vaptcha({
                    vid:            '<?php echo esc_textarea($vid); ?>',
                    mode:           '<?php echo esc_textarea( get_option('toms_vaptcha_mode', 'click') ); ?>',
                    scene:          0, 
                    container:      '<?php echo esc_textarea($sign.$vaptcha_form_name.$suffix); ?>',
                    style:          '<?php echo esc_textarea($button_style); ?>',
                    color:          '<?php echo esc_textarea($button_color); ?>',
                    lang:           '<?php echo esc_textarea($lang); ?>',
                    https:          '<?php echo esc_textarea($https); ?>',
                    area:           '<?php echo esc_textarea($area); ?>',
                    guide:          '<?php echo esc_textarea($guide); ?>'
                }).then(
                    function( vaptchaObj ){                   
                        Obj = vaptchaObj;
                        <?php if(get_option('toms_vaptcha_mode') != 'invisible'): ?>
                        Obj.render();
                        //Obj.renderTokenInput("<?php echo esc_textarea($vaptcha_form); ?>");
                        <?php endif; ?>

                        document.getElementsByClassName("<?php echo esc_attr( $submit_class )?>")[0].classList.add("toms-vaptcha-not-allowed");
                        document.getElementsByClassName("<?php echo esc_attr( $submit_class )?>")[0].setAttribute("disabled", true);
                        document.getElementsByClassName("<?php echo esc_attr( $submit_class )?>")[0].setAttribute("title", "<?php get_option('toms_vaptcha_mode') != 'invisible' ? _e('Please press the captcha to verify！', 'toms-vaptcha') : _e('Press the form area to active the captcha to verify！', 'toms-vaptcha'); ?>");
                        document.getElementById("<?php echo esc_attr( $nonce_name )?>").removeAttribute("name");
                        document.getElementById("<?php echo esc_attr( $nonce_name )?>").setAttribute("name", "nonce");

                        Obj.listen('pass', function () {
                            
                            data = Obj.getServerToken();

                            if(!!(data.server) && !!(data.token)) {
                                document.getElementsByClassName("<?php echo esc_attr( $submit_class )?>")[0].classList.remove("toms-vaptcha-not-allowed");
                                document.getElementsByClassName("<?php echo esc_attr( $submit_class )?>")[0].classList.add("toms-vaptcha-allowed");
                                document.getElementsByClassName("<?php echo esc_attr( $submit_class )?>")[0].removeAttribute("disabled");
                                document.getElementsByClassName("<?php echo esc_attr( $submit_class )?>")[0].removeAttribute("title");
                                document.getElementById("<?php echo esc_attr( $nonce_name )?>").removeAttribute("name");
                                document.getElementById("<?php echo esc_attr( $nonce_name )?>").setAttribute("name", "<?php echo esc_attr( $nonce_name )?>");
                                // document.getElementById("toms-vaptcha-error").remove();
                                
                                <?php if(get_option('toms_vaptcha_mode') == 'invisible'): ?>
                                    setTimeout(function() {
                                        document.getElementsByClassName("<?php echo esc_attr( $submit_class )?>")[0].classList.add("toms-vaptcha-not-allowed");
                                        document.getElementsByClassName("<?php echo esc_attr( $submit_class )?>")[0].classList.remove("toms-vaptcha-allowed");
                                        document.getElementsByClassName("<?php echo esc_attr( $submit_class )?>")[0].setAttribute("disabled", true);
                                        document.getElementsByClassName("<?php echo esc_attr( $submit_class )?>")[0].setAttribute("title", "<?php get_option('toms_vaptcha_mode') != 'invisible' ? _e('Please press the captcha to verify！', 'toms-vaptcha') : _e('Press the form area to active the captcha to verify！', 'toms-vaptcha'); ?>");
                                        document.getElementById("<?php echo esc_attr( $nonce_name )?>").removeAttribute("name");
                                        document.getElementById("<?php echo esc_attr( $nonce_name )?>").setAttribute("name", "nonce");
                                        Obj.reset();
                                    }, 180000);
                                <?php endif; ?>
                            }
                        })

                        <?php if(get_option('toms_vaptcha_mode') == 'invisible'): ?>
                            //add click event to whole forms for invisible mode
                            var TomSClick = document.<?php echo esc_html($element); ?>("<?php echo esc_html($vaptcha_form_name); ?>")<?php echo esc_html($number)?>;
                            
                            TomSClick.addEventListener('click', function() {
                                Obj.validate();
                            });

                            //If the Close button manually close, reset the verification.
                            Obj.listen('close', function () {
                                Obj.reset();
                            })
                        <?php endif; ?>
                    }
                )
                
            <?php  
            $html = ob_get_contents();
            ob_end_clean();

            return $html ;
        }
    }
}
