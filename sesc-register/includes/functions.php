<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function registration_form( $username, $password, $email, $cpf ) {
    $login_botao = get_theme_mod( 'mf45_votacao_login_botao' );
    $login_input_nome_label = get_theme_mod( 'mf45_votacao_login_input_nome_label' );
    $login_input_nome_placeholder = get_theme_mod( 'mf45_votacao_login_input_nome_placeholder' );
    $login_input_nome_ajuda = get_theme_mod( 'mf45_votacao_login_input_nome_ajuda' );
    $login_input_email_label = get_theme_mod( 'mf45_votacao_login_input_email_label' );
    $login_input_email_placeholder = get_theme_mod( 'mf45_votacao_login_input_email_placeholder' );
    $login_input_email_ajuda = get_theme_mod( 'mf45_votacao_login_input_email_ajuda' );
    $login_input_cpf_label = get_theme_mod( 'mf45_votacao_login_input_cpf_label' );
    $login_input_cpf_placeholder = get_theme_mod( 'mf45_votacao_login_input_cpf_placeholder' );
    $login_input_cpf_ajuda = get_theme_mod( 'mf45_votacao_login_input_cpf_ajuda' );

    
    $form = '<form id="login-form" action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                <div class="login--form--grupo">' ;   
        
    $form .= ( $login_input_nome_label )?'<label class="login--form--etiqueta">'.  $login_input_nome_label.'</label>':'';
    
    $form .= '<input class="login--form--input __nome" name="username" id="username" required placeholder="' 
          . $login_input_nome_placeholder . '" value= "' . ( isset( $_POST['username'] ) ? $username : null ) . ' " />';
   
    $form .= ( $login_input_nome_ajuda )?'<p class="login--form--ajuda">* '.  $login_input_nome_ajuda.'</p>':'';
 	
    $form .=  '</div>';
  
    $form .=  '<div class="login--form--grupo">';
    
    $form .= ( $login_input_email_label )?'<label class="login--form--etiqueta">'.  $login_input_email_label.'</label>':'';
    
    $form .= '<input class="login--form--input __email" name="email" id="email" placeholder="' . $login_input_email_placeholder
            . '" required type="email" value="' . ( isset( $_POST['email']) ? $email : null ) . ' " />';
    
    $form .= ( $login_input_email_ajuda )?'<p class="login--form--ajuda">*'.  $login_input_email_ajuda.'</p>':'';
    
    $form .=  '</div>';
    
    $form .=  '<div class="login--form--grupo">';
    
    $form .= ( $login_input_cpf_label )?'<label class="login--form--etiqueta">'.  $login_input_cpf_label.'</label>':'';
    
    $form .= '<input class="login--form--input __cpf" name="cpf"  id="cpf"  placeholder="' . $login_input_cpf_placeholder
            . '" required value="' . ( isset( $_POST['cpf']) ? $cpf : null ) . ' " />';
    
    $form .= ( $login_input_cpf_ajuda )?'<p class="login--form--ajuda">* '.  $login_input_cpf_ajuda.'</p>':'';
    
    $form .=  '</div>';
    
    $form .= ( $login_botao )?'<div class="login--botoes"><input class="login--botao" type="submit" name="submit"  id="submit"  value="'.  $login_botao.'" />':'';
    $form .=  '</div>';

    
    echo $form ;
        
         
}


function registration_validation( $username,  $email, $cpf )  {
    global $reg_errors;
    $reg_errors = new WP_Error;
    global $wpdb;
    
    if ( empty( $username )  || empty( $email ) || empty( $cpf ) ) {
        $reg_errors->add('field', 'É necessário preencher todos os campos.');
        
    }
    
    if ( 4 > strlen( $username ) && strlen( $username ) > 1 ) {
        $reg_errors->add( 'username_length', 'Mínimo de 4 caracteres para o campo de Nome.' );
    }
    
    if ( strlen( $email ) > 1 ) {
        
         if ( !is_email( $email )){
            $reg_errors->add( 'email_invalid', 'E-mail inválido' ); 
         }else{
//             $email_exists = email_exists( $email );
//             if ( $email_exists ) {
//                 $cpf_exists = get_user_meta( $email_exists, 'cpf', TRUE ); 
//                 if (!$cpf_exists || $cpf_exists !== $cpf){
//                     $reg_errors->add( 'email_invalid', 'Este e-mail já está sendo usado.' );
//                 }
//             }
  
         }
        
    }

    if ( strlen( $cpf ) > 1 ) {
 
        $user_row = $wpdb->get_results( 
            $wpdb->prepare( "SELECT user_email FROM {$wpdb->prefix}users users
                        INNER JOIN {$wpdb->prefix}usermeta usermeta ON
                        users.ID = usermeta.user_id
                        WHERE (usermeta.meta_key = 'cpf' and meta_value = '%s')",
                        $cpf ) );
        if ( ! empty( $user_row ) ) {
            if ($user_row[0]->user_email !== $email){
                $reg_errors->add( 'cpf_invalid', 'Este CPF já está cadastrado para outro e-mail' );
            }
        }
           
        
    }
    
    if ( is_wp_error( $reg_errors ) ) {

        foreach ( $reg_errors->get_error_messages() as $error ) {

            echo '<div class="login--form--erro">';
            echo '<strong>ERRO</strong>: ';
            echo $error . '<br/>';
            echo '</div>';

        }

    }    
}

function complete_registration() {
    global $reg_errors, $username, $password, $email, $cpf; 
    if ( 1 > count( $reg_errors->get_error_messages() ) ) {
        
        while ( username_exists( $username ) ){
             $username .=  '_';
        }
        
        $userdata = array(
        'user_login'    =>   $username,
        'user_email'    =>   $email,
        'user_pass'     =>   $password
        );
 

        global $wpdb;
        
        $user_row = $wpdb->get_results( 
            $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}users users
                        INNER JOIN {$wpdb->prefix}usermeta usermeta ON
                        users.ID = usermeta.user_id
                        WHERE users.user_email = '%s'
                        AND (usermeta.meta_key = 'cpf' and meta_value = '%s')",
                        $email, $cpf ) );

        if ( ! empty( $user_row ) ) {
            
            $user_id = $user_row[0]->ID;
            
         }  else {
              error_log($username); 
            $user_id = wp_insert_user( $userdata );
            
            add_user_meta( $user_id, 'cpf', $cpf );
           

        } 
        
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        $user = get_user_by( 'id', $user_id);
        do_action( 'wp_login', $user->user_login );
        wp_redirect( home_url('votacao') ); 
        exit;         
 
    }
}


function custom_registration_function() {
    
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST ) ) {
       
                
        registration_validation(
        $_POST['username'],
        $_POST['email'],
        $_POST['cpf']
        );
        
        
        // sanitize user form input
        global $username, $password, $email, $cpf;
        $username   =   sanitize_user( $_POST['username'] );
        $password   =   $_POST['cpf'] . '123456';
        $email      =   sanitize_email( $_POST['email'] );
        $cpf        =   sanitize_text_field ( $_POST['cpf'] );
      
        
        // call @function complete_registration to create the user
        // only when no WP_error is found
        complete_registration(
        $username,
        $password,
        $email,
        $cpf
        );
    }
 
    registration_form(
        ((isset($username))?$username:''),
        ((isset($password))?$password:''),
        ((isset($email))?$email:''),
        ((isset($cpf))?$cpf:'')
        );
}


add_filter('pre_user_email', 'skip_email_exist');
function skip_email_exist($user_email){
    define( 'WP_IMPORTING', 'SKIP_EMAIL_EXIST' );
    return $user_email;
}

// Register a new shortcode: [cr_custom_registration]
add_shortcode( 'cr_custom_registration', 'custom_registration_shortcode' );
 
// The callback function that will replace [book]
function custom_registration_shortcode() {
    ob_start();
    custom_registration_function();
    return ob_get_clean();
}

