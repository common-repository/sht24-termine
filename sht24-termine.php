<?php
/*
 * Plugin Name: sicherheitstraining24.de Termine & Buchung
 * Description: Integrieren Sie eine Liste von Trainings der Plattform sicherheitstraining24.de. Das Plugin erstellt filterbare Listen, aus denen Sie direkt auf die Detail und Buchungsseite des jeweiligen Trainings auf sicherheitstraining24.de verlinken können. Die Tabellenansicht kann nach Ihren Bedürfnissen angepasst werden. Es können mehrere Traingsplätze zu einer Gesamtansicht zusammengeführt werden.
 * Version: 1.0.4
 * Author: Bartzik Webdesign
 * Author URI: https://webdesign.bartzik.net/
 * Text Domain: sht24
 * Domain Path: /locale/
 * License: MIT
*/

/* deny direct access to php file */
defined( 'ABSPATH' ) or die( 'Direct access is forbidden!' );

/* register css files */
wp_register_style( 'sht24', plugins_url('/assets/css/sht24.min.css', __FILE__ ) );
wp_register_style( 'DataTables', plugins_url('/DataTables/datatables.min.css', __FILE__ ) );

/* enqueue css files */
wp_enqueue_style( 'sht24' );
wp_enqueue_style( 'DataTables' );

/* register js files */
wp_register_script( 'DataTables', plugins_url('/DataTables/datatables.min.js', __FILE__ ), array( 'jquery'), '', true );
wp_register_script( 'sht24', plugins_url('/assets/js/sht24.min.js', __FILE__ ), array( 'jquery'), '', true );

/* enqueue js files */
wp_enqueue_script( 'DataTables' );
wp_enqueue_script( 'sht24' );

/* localize js files */
wp_localize_script( 'sht24', 'sht24_object',
    array( 
        'plugin_directory' => plugin_dir_url( __FILE__ ),
        'defaultOrderColumnNumber' => ((get_option('show_programm_icons') == 1) ? 1 : 0)
    )
);

/* define global variables */
/* set list of training grounds ids */
$ground_ids = esc_attr( get_option('ground_ids') );

/* generate merge array */
$merged_trainings = array();
$merged_trainings_by_category_type = array();

/* define message to be shwon below each shortcode block */
$message_below = esc_attr( get_option('message_below') );

/* define dayname display */
$dayname[0] = "So.";
$dayname[1] = "Mo.";
$dayname[2] = "Di.";
$dayname[3] = "Mi.";
$dayname[4] = "Do.";
$dayname[5] = "Fr.";
$dayname[6] = "Sa.";

/* define icons by training type */
$icon["Pkw Basistraining | DVR"]                        = "<i class=\"sht24-programmicon sht24-pkw-8h\" title=\"Pkw Basistraining | DVR\"></i>";
$icon["Eco-Sicherheitstraining"]                        = "<i class=\"sht24-programmicon sht24-sonstige\" title=\"Eco-Sicherheitstraining\"></i>";
$icon["Pkw Junge Fahrer | BF17"]                        = "<i class=\"sht24-programmicon sht24-pkw-bf17\" title=\"Pkw Junge Fahrer | BF17\"></i>";
$icon["Könner durch Erfahrung - Motorrad"]              = "<i class=\"sht24-programmicon sht24-sonstige\" title=\"Könner durch Erfahrung - Motorrad\"></i>";
$icon["Könner durch Erfahrung - Pkw"]                   = "<i class=\"sht24-programmicon sht24-sonstige\" title=\"Könner durch Erfahrung - Pkw\"></i>";
$icon["Wohnmobil-Training (DWC)"]                       = "<i class=\"sht24-programmicon sht24-wohnmobil\" title=\"Wohnmobil-Training (DWC)\"></i>";
$icon["Wohnwagen-Training (DWC)"]                       = "<i class=\"sht24-programmicon sht24-wohnwagen\" title=\"Wohnwagen-Training (DWC)\"></i>";
$icon["Berufskraftfahrer-Weiterbildung (BKrFQG)"]       = "<i class=\"sht24-programmicon sht24-sonstige\" title=\"Berufskraftfahrer-Weiterbildung (BKrFQG)\"></i>";
$icon["Motorradtraining"]                               = "<i class=\"sht24-programmicon sht24-motorrad\" title=\"Motorradtraining\"></i>";
$icon["Motorradtraining im Straßenverkehr"]             = "<i class=\"sht24-programmicon sht24-motorrad-kurve\" title=\"Motorradtraining im Straßenverkehr\"></i>";
$icon["Einsatzfahrzeugtraining"]                        = "<i class=\"sht24-programmicon sht24-einsatzfahrzeug\" title=\"Einsatzfahrzeugtraining\"></i>";
$icon["Ladungssicherung"]                               = "<i class=\"sht24-programmicon sht24-ladungssicherung\" title=\"Ladungssicherung\"></i>";
$icon["Linienbustraining"]                              = "<i class=\"sht24-programmicon sht24-linienbus\" title=\"Linienbustraining\"></i>";
$icon["Lkw Training"]                                   = "<i class=\"sht24-programmicon sht24-lkw\" title=\"Lkw Training\"></i>";
$icon["Reisebustraining"]                               = "<i class=\"sht24-programmicon sht24-reisebus\" title=\"Reisebustraining\"></i>";
$icon["Tankwagentraining"]                              = "<i class=\"sht24-programmicon sht24-tanklaster\" title=\"Tankwagentraining\"></i>";
$icon["Transporter FQT"]                                = "<i class=\"sht24-programmicon sht24-transporter-fqt\" title=\"Transporter FQT\"></i>";
$icon["Transportertraining"]                            = "<i class=\"sht24-programmicon sht24-transporter\" title=\"Transportertraining\"></i>";
$icon["Geländewagen"]                                   = "<i class=\"sht24-programmicon sht24-gelaendewagen\" title=\"Geländewagen\"></i>";
$icon["Pkw Seniorentraining"]                           = "<i class=\"sht24-programmicon sht24-pkw-senioren\" title=\"Pkw Seniorentraining\"></i>";
$icon["Pkw Kompakttraining"]                            = "<i class=\"sht24-programmicon sht24-pkw-4-5h\" title=\"Pkw Kompakttraining\"></i>";
$icon["Motorrad Aufbautraining"]                        = "<i class=\"sht24-programmicon sht24-motorrad-aufbau\" title=\"Motorrad Aufbautraining\"></i>";
$icon["Motorrad Kurven- und Schräglagentraining"]       = "<i class=\"sht24-programmicon sht24-motorrad-schraeglage\" title=\"Motorrad Kurven- und Schräglagentraining\"></i>";
$icon["Motorrad Kurventraining"]                        = "<i class=\"sht24-programmicon sht24-motorrad-schraeglage\" title=\"Motorrad Kurventraining\"></i>";
$icon["Pkw Aufbautraining | DVR"]                       = "<i class=\"sht24-programmicon sht24-pkw-aufbau\" title=\"Pkw Aufbautraining | DVR\"></i>";
$icon["Wohnmobil Training (unter 3,5t)"]                = "<i class=\"sht24-programmicon sht24-wohnmobil\" title=\"Wohnmobil Training (unter 3,5t)\"></i>";
$icon["Caravan Training"]                               = "<i class=\"sht24-programmicon sht24-wohnwagen\" title=\"Caravan Training\"></i>";
$icon["Motorradtraining mit Kartbahn-Kurventraining"]   = "<i class=\"sht24-programmicon sht24-motorrad-schraeglage\" title=\"Motorradtraining mit Kartbahn-Kurventraining\"></i>";

/* get from array with default function */
function sht24termine_get($value, $default = null) {
    return isset($value) ? $value : $default;
}

/* get available spaces level class */
function sht24termine_getLevelClass($value, $red = 0, $orange = 3) {
    if ($value == $red) {
        return "sht24-level-red";
    } elseif ($value > $red && $value <= $orange) {
        return "sht24-level-orange";
    } else {
        return "sht24-level-green";
    }
}

/* define reciever function */
function sht24termine_recieveTrainingsFromGroundIDs( $ids = null ) {
    global $ground_ids;
    global $merged_trainings;
    global $merged_trainings_by_category_type;
    
    $merged_trainings = null;
    $merged_trainings_by_category_type = null;
    
    /* check if $ids is given, otherwise use global ground_ids from plugin options page instead */
    if($ids == null) {
        /* set ids to global ground_ids from plugin options page */
        $ids = explode(",", $ground_ids);
    } else {
        /* filter list of ground_ids to numeric values only and put into array */
        $ids = array_filter(explode(",", preg_replace("/[^0-9,]/", "", $ids)));
    }
    
    /* loop through all given ground_ids */
    foreach($ids as $ground_id) {        
        /* setup and do the request */
        $result = wp_remote_retrieve_body( wp_remote_get( 'https://sicherheitstraining24.de/trainingsplaetze/' . $ground_id . '.json' ) );
        
        /* decode the result from json */
        $obj = json_decode($result);
        
        /* check if error was recieved, on error abort */
        if(!$obj->error) {
            /* decode recieved object to an array */
            $as_array = (array) $obj->trainings;
            
            /* loop through every training in recieved array */
            foreach ($as_array as $index => $value) {
                /* decode recieved object to an array */
                $value = json_decode(json_encode($value), true);
                
                /* add some more information to every single training about the training ground */
                $value["ground_id"] = $obj->id;
                $value["ground_name"] = htmlspecialchars($obj->name);
                $value["ground_url"] = "https://sicherheitstraining24.de/trainingsplaetze/" . $obj->id;

                /* put the result into global variables */
                $merged_trainings[] = $value;
                $merged_trainings_by_category_type[$value["category"]][$value["type"]][] = $value;
            }
        }
    }
}


/* register plugin options function */
function sht24termine_register_settings() {
   // add_option( 'sht24termine_options', 'This is my option value.');
   register_setting( 'sht24termine_options', 'ground_ids', 'sht24termine_options_validation_ground_ids');
   register_setting( 'sht24termine_options', 'message_below', 'sht24termine_options_validation_message_below');
   register_setting( 'sht24termine_options', 'show_capacity', 'sht24termine_options_validation_checkbox');
   register_setting( 'sht24termine_options', 'show_price', 'sht24termine_options_validation_checkbox');
   register_setting( 'sht24termine_options', 'show_programm_icons', 'sht24termine_options_validation_checkbox');
}

/* hook sht24termine_register_settings function */
add_action( 'admin_init', 'sht24termine_register_settings' );


/* register plugin options page for wordpress administration function */
function sht24termine_register_options_page() {
  add_options_page('sicherheitstraining24.de Termine &amp; Buchung', 'SHT24 Termine &amp; Buchung', 'manage_options', 'sht24-termine', 'sht24termine_options_page');
}

/* hook sht24termine_register_options_page function */
add_action('admin_menu', 'sht24termine_register_options_page');

/* generate link to plugin settingspage on plugin overview page */
function sht24termine_settings_link( $links ) {
	/* Build and escape the url */
	$url = esc_url( add_query_arg(
		'page',
		'sht24-termine',
		get_admin_url() . 'options-general.php'
	) );
	/* Create the link */
	$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
	/* Adds the link to the end of the array */
	array_push(
		$links,
		$settings_link
	);
	return $links;
}

/* hook sht24termine_settings_link function */
add_filter( 'plugin_action_links_sht24-termine/sht24-termine.php', 'sht24termine_settings_link' );

/* validate / filter input 'ground_ids' function */
function sht24termine_options_validation_ground_ids($input) {
    $options =  preg_replace("/[^0-9,]/", "", esc_attr( $input ));
    $options =  implode(",", array_filter(explode(",", $options)));
    return $options;
}

/* validate / filter input 'message_below' function */
function sht24termine_options_validation_message_below($input) {
    $options = esc_attr( $input );
    return $options;
}

/* validate checkboxes function */
function sht24termine_options_validation_checkbox($input) {
    switch ($input) {
        case 1:
        case true:
            $options = 1;
            break;
        case 0:
        case false:
        default:
            $options = 0;
            break;
    }
    return $options;
}

/* display plugin options page function */
function sht24termine_options_page() {
    echo "<div>";
    echo " <h1>sicherheitstraining24.de Termine &amp; Buchung</h1>";
    echo " <p>&nbsp;</p>";
    echo " <h2>Einstellungen</h2>";
    echo " <p>Hier können Sie zentrale Einstellungen für das Plugin &quot;sicherheitstraining24.de Termine & Buchung&quot; vornehmen.</p>";
    echo " <form method=\"post\" action=\"options.php\">";
    
    /* init plugin settings */
    settings_fields( 'sht24termine_options' );
    do_settings_sections( 'sht24termine_options' );
    
    echo "  <table class=\"form-table\">";
    echo "   <tr valign=\"top\">";
    echo "    <th scope=\"row\"><label for=\"ground_ids\">Trainingsplatz-IDs</label></th>";
    echo "    <td>";
    echo "     <input type=\"text\" name=\"ground_ids\" id=\"ground_ids\" class=\"regular-text\" value=\"" . esc_attr( get_option('ground_ids') ) . "\" />";
    echo "     <p class=\"description\">Geben Sie eine Liste aus Trainingsplatz-IDs ein, die standardmäßig abgefragt werden sollen. Einzelne IDs können Sie mit <code>,</code> von einander trennen.</p>";
    echo "     <p class=\"description\">Die jeweilige Trainingsplatz-ID erhalten finden Sie im Link des Trainingsplatzes auf sicherheitstraining24.de:</p>";
    echo "     <p class=\"description\"><code>https://sicherheitstraining24.de/trainingsplaetze/<b><u>123</u></b></code></p>";
    echo "    </td>";
    echo "   </tr>";
    echo "   <tr valign=\"top\">";
    echo "    <th scope=\"row\"><label for=\"show_programm_icons\">Programm-Icons anzeigen</label></th>";
    echo "    <td>";
    echo "     <input type=\"checkbox\" name=\"show_programm_icons\" id=\"show_programm_icons\" class=\"regular-text\" value=\"true\" " . ((get_option('show_programm_icons') == 1) ? "checked" : "") . " />";
    echo "     <p class=\"description\">Sollen die jeweiligen Programm-Icons angezeigt werden?</p>";
    echo "    </td>";
    echo "   </tr>";
    echo "   <tr valign=\"top\">";
    echo "    <th scope=\"row\"><label for=\"show_capacity\">Gesamtteilnehmerzahl anzeigen</label></th>";
    echo "    <td>";
    echo "     <input type=\"checkbox\" name=\"show_capacity\" id=\"show_capacity\" class=\"regular-text\" value=\"true\" " . ((get_option('show_capacity') == 1) ? "checked" : "") . " />";
    echo "     <p class=\"description\">Soll neben der Anzahl der freien Teilnehmerplätze auch die Gesamtzahl der Plätze angezeigt werden?</p>";
    echo "    </td>";
    echo "   </tr>";
    echo "   <tr valign=\"top\">";
    echo "    <th scope=\"row\"><label for=\"show_price\">Preisangabe anzeigen</label></th>";
    echo "    <td>";
    echo "     <input type=\"checkbox\" name=\"show_price\" id=\"show_price\" class=\"regular-text\" value=\"true\" " . ((get_option('show_price') == 1) ? "checked" : "") . " />";
    echo "     <p class=\"description\">Soll der Standardtrainingspreis angezeigt werden?</p>";
    echo "    </td>";
    echo "   </tr>";
    echo "   <tr valign=\"top\">";
    echo "    <th scope=\"row\"><label for=\"message_below\">Hinweistext</label></th>";
    echo "    <td>";
    echo "     <textarea class=\"large-text\" name=\"message_below\" id=\"message_below\">" . esc_attr( get_option('message_below') ) . "</textarea>";
    echo "     <p class=\"description\">Geben Sie hier einen Hinweistext ein, der unterhalb der Trainingstabellen angezeigt werden soll. Hier kann besipielsweise darauf hingewiesen werden, dass der Besucher für die Buchung auf die Seite sicherheitstraining24.de weitergeleitet wird.</p>";
    echo "    </td>";
    echo "   </tr>";
    echo "  </table>";
    
    /* print out preformated wordpress options save button */
    submit_button();
    
    echo " </form>";
    echo " <hr />";
    echo " <h2>Shortcodes</h2>";
    echo " <p>";
    echo "  Nutzen Sie für die Anzeige der Trainings den Shortcode <code>[sht24]</code> innerhalb von Seiten oder Beiträgen.<br />";
    echo "  Ohne weitere Einstellungen werden alle Trainings angezeigt, die auf den oben definierten Trainingsplätzen stattfinden.";
    echo " </p>";
    echo " <p>Über folgende Attribute können Sie die Anzeige des jeweiligen Blocks anpassen:</p>";
    echo " <table class=\"form-table\">";
    
    echo "  <tr valign=\"top\">";
    echo "   <th scope=\"row\">Überschrift hinzufügen:</th>";
    echo "   <td>";
    echo "    <code>[sht24 headline=\"Unsere PKW-Trainings\"]</code>";
    echo "    <p class=\"description\">Nutzen Sie das Attribut <code>headline</code> um eine Überschrift oberhalb der Tabelle anzuzeigen.</p>";
    echo "   </td>";
    echo "  </tr>";
    
    echo "  <tr valign=\"top\">";
    echo "   <th scope=\"row\">Nur individuelle Trainingsplätze anzeigen:</th>";
    echo "   <td>";
    echo "    <code>[sht24 ground_ids=\"1, 123, 7\"]</code>";
    echo "    <p class=\"description\">Nutzen Sie das Attribut <code>ground_ids</code> um nur die hier spezifizierten Trainingsplätze abzufragen.</p>";
    echo "   </td>";
    echo "  </tr>";
    
    echo "  <tr valign=\"top\">";
    echo "   <th scope=\"row\">Nur Trainings einer Kategorie anzeigen:</th>";
    echo "   <td>";
    echo "    <code>[sht24 category=\"PKW\"]</code>";
    echo "    <p class=\"description\">Nutzen Sie das Attribut <code>category</code> um nur die hier spezifizierte Kategroie anzuzeigen.</p>";
    echo "   </td>";
    echo "  </tr>";
    
    echo "  <tr valign=\"top\">";
    echo "   <th scope=\"row\">Nur Trainings einer Kategorie und eines Trainingstyps anzeigen:</th>";
    echo "   <td>";
    echo "    <code>[sht24 category=\"PKW\" type=\"Pkw Kompakttraining\"]</code>";
    echo "    <p class=\"description\">Nutzen Sie das Attribut <code>type</code> in Verbindung mit dem Attribut <code>category</code> um nur den hier spezifizierten Trainingstyp anzuzeigen. Das Attribut <code>type</code> muss in Verbindung mit dem Attribut <code>category</code> verwendet werden.</p>";
    echo "   </td>";
    echo "  </tr>";
    
    echo "  <tr valign=\"top\">";
    echo "   <th scope=\"row\">Kombinationen:</th>";
    echo "   <td>";
    echo "    <code>[sht24 headline=\"PKW Kompakttrainings der VW Musterstadt\" category=\"PKW\" type=\"Pkw Kompakttraining\" ground_ids=\"123\"]</code>";
    echo "    <p class=\"description\">Sie können die vorgenannten Attribute kombinieren.</p>";
    echo "   </td>";
    echo "  </tr>";
    
    echo " </table>";
    echo "</div>";
}

/* register sht24-shortcode handler function */
add_shortcode( 'sht24', 'sht24_shortcode_handler_function' );

/* define sht24-shortcode handler function
 * [ATTS] category      (string)    training category
 * [ATTS] type          (string)    training type
 * [ATTS] headline      (string)    table headline
 * [ATTS] ground_ids    (string)    list of ground ids seperated by comma
 */
function sht24_shortcode_handler_function( $atts, $content, $tag ) {
    global $ground_ids;
    global $merged_trainings;
    global $merged_trainings_by_category_type;
    global $dayname;
    global $icon;
    global $message_below;
    
    /* do sht24termine_recieveTrainingsFromGroundIDs function */
    sht24termine_recieveTrainingsFromGroundIDs( $atts["ground_ids"] );
    
    /* switch the case, which data array is needed now */
    if( $atts["category"] != "" ) {
        if( $atts["type"] != "" ) {
            $output_arr = $merged_trainings_by_category_type[$atts["category"]][$atts["type"]];
        } else {
            $output_arr = $merged_trainings_by_category_type[$atts["category"]];
        }
    } else {
        $output_arr = $merged_trainings;
    }
    
    /* do sorting by date asc, start_time asc and type asc */
    $sort = array();
    foreach($output_arr as $k => $v) {
        $sort["date"][$k] = $v["date"];
        $sort["start_time"][$k] = $v["start_time"];
        $sort["type"][$k] = $v["type"];
    }
    array_multisort($sort['date'], SORT_ASC, $sort['start_time'], SORT_ASC, $sort['type'], SORT_ASC, $output_arr);
    
    /* count visible columns fpr colspan attribute */
    $colspan = 4;
    if(get_option('show_programm_icons') == 1) $colspan++;
    if(get_option('show_price') == 1) $colspan++;
    
    /* generate the output */
    $output  = "<div class=\"sht24-responsive-table\">";
    
    if( $atts["headline"] != "" ) {
        $output .= " <h3>" . $atts["headline"] . "</h3>";
    }
    
    $output .= " <table class=\"sht24-trainingslist dt-responsive\" style=\"max-width: 100%;\">";
    $output .= "  <thead>";
    if(get_option('show_programm_icons') == 1) $output .= "   <th class=\"no-sort\" data-priority=\"1\">Icon</th>";
    $output .= "   <th data-priority=\"1\">Datum / Uhrzeit</th>";
    $output .= "   <th data-priority=\"2\">Trainingsprogramm</th>";
    $output .= "   <th data-priority=\"3\" class=\"sht24-text-center\">Freie Plätze</th>";
    if(get_option('show_price') == 1) $output .= "   <th data-priority=\"3\">Preis</th>";
    $output .= "   <th data-priority=\"2\" class=\"no-sort\">Aktionen</th>";
    $output .= "  </thead>";
    $output .= "  <tbody>";
    
    /* fill the output table with filtered and sortet training data */
    if(is_array($output_arr) && count($output_arr) > 0) {
        foreach($output_arr as $index => $value) {
            if( $atts["category"] != "" ) {
                if( $atts["type"] != "" ) {
                    /* give out all trainings filtered by category and type */
                    $output .= "   <tr>";
                    if(get_option('show_programm_icons') == 1) $output .= "    <td>" . sht24termine_get(@$icon[$value["type"]],  "<i class=\"programmicon sonstige\" title=\"" . $value["type"] . "\"></i>") . "</td>";
                    $output .= "    <td class=\"sht24-nowrap\"><span style=\"display:none;\">" . date_format(date_create($value["date"]), "Y-m-d") . "</span>" . $dayname[date_format(date_create($value["date"]), "w")] . ", " . date_format(date_create($value["date"]), "d.m.Y") . "<br>" . $value["start_time"] . " - " . $value["end_time"] . " Uhr</td>";
                    $output .= "    <td>" . $value["type"] . "</td>";
                    $output .= "    <td class=\"sht24-nowrap sht24-text-center\"><span class=\"" . sht24termine_getLevelClass($value["available_space"]) . "\">" . $value["available_space"] . ((get_option('show_capacity') == 1 && $value["total_spaces"] > 0) ? " / " . $value["total_spaces"] : "") . "</span></td>";
                    if(get_option('show_price') == 1) $output .= "    <td class=\"sht24-nowrap\">" . number_format($value["price_per_person"], 2, ",", ".") . " &euro;</td>";
                    $output .= "    <td class=\"sht24-nowrap\">";
                    $output .= "     <a href=\"" . $value["training_details_url"] . "\" data-title=\"Trainingsdetails ansehen auf sicherheitstraining24.de\" class=\"sht24-info-btn sht24-tooltip\" target=\"_blank\"><i class=\"sht24-i-info\"></i></a> ";
                    $output .= "     <a href=\"" . $value["ground_url"] . "\" data-title=\"" . $value["ground_name"] . "\" class=\"sht24-location-btn sht24-tooltip\" target=\"_blank\"><i class=\"sht24-i-map-marker\"></i></a> ";
                    $output .= "     <a href=\"" . $value["training_booking_url"] . "\" data-title=\"Jetzt auf sicherheitstraining24.de buchen\" class=\"sht24-booking-btn sht24-tooltip " . (($value["available_space"] == 0) ? "sht24-d-none" : "") . "\" target=\"_blank\"><i class=\"sht24-i-booking\"></i></a>";
                    $output .= "    </td>";
                    $output .= "   </tr>";
                } else {
                    /* give out all trainings filtered by category only */
                    foreach($value as $index => $value) {
                        $output .= "   <tr>";
                        if(get_option('show_programm_icons') == 1) $output .= "    <td>" . sht24termine_get(@$icon[$value["type"]],  "<i class=\"programmicon sonstige\" title=\"" . $value["type"] . "\"></i>") . "</td>";
                        $output .= "    <td class=\"sht24-nowrap\"><span style=\"display:none;\">" . date_format(date_create($value["date"]), "Y-m-d") . "</span>" . $dayname[date_format(date_create($value["date"]), "w")] . ", " . date_format(date_create($value["date"]), "d.m.Y") . "<br>" . $value["start_time"] . " - " . $value["end_time"] . " Uhr</td>";
                        $output .= "    <td>" . $value["type"] . "</td>";
                        $output .= "    <td class=\"sht24-nowrap sht24-text-center\"><span class=\"" . sht24termine_getLevelClass($value["available_space"]) . "\">" . $value["available_space"] . ((get_option('show_capacity') == 1 && $value["total_spaces"] > 0) ? " / " . $value["total_spaces"] : "") . "</span></td>";
                        if(get_option('show_price') == 1) $output .= "    <td class=\"sht24-nowrap\">" . number_format($value["price_per_person"], 2, ",", ".") . " &euro;</td>";
                        $output .= "    <td class=\"sht24-nowrap\">";
                        $output .= "     <a href=\"" . $value["training_details_url"] . "\" data-title=\"Trainingsdetails ansehen auf sicherheitstraining24.de\" class=\"sht24-info-btn sht24-tooltip\" target=\"_blank\"><i class=\"sht24-i-info\"></i></a> ";
                        $output .= "     <a href=\"" . $value["ground_url"] . "\" data-title=\"" . $value["ground_name"] . "\" class=\"sht24-location-btn sht24-tooltip\" target=\"_blank\"><i class=\"sht24-i-map-marker\"></i></a> ";
                        $output .= "     <a href=\"" . $value["training_booking_url"] . "\" data-title=\"Jetzt auf sicherheitstraining24.de buchen\" class=\"sht24-booking-btn sht24-tooltip " . (($value["available_space"] == 0) ? "sht24-d-none" : "") . "\" target=\"_blank\"><i class=\"sht24-i-booking\"></i></a>";
                        $output .= "    </td>";
                        $output .= "   </tr>";
                    }
                }
            } else {
                /* give out all trainings without any filter */
                $output .= "   <tr>";
                if(get_option('show_programm_icons') == 1) $output .= "    <td>" . sht24termine_get(@$icon[$value["type"]],  "<i class=\"programmicon sonstige\" title=\"" . $value["type"] . "\"></i>") . "</td>";
                $output .= "    <td class=\"sht24-nowrap\"><span style=\"display:none;\">" . date_format(date_create($value["date"]), "Y-m-d") . "</span>" . $dayname[date_format(date_create($value["date"]), "w")] . ", " . date_format(date_create($value["date"]), "d.m.Y") . "<br>" . $value["start_time"] . " - " . $value["end_time"] . " Uhr</td>";
                $output .= "    <td>" . $value["type"] . "</td>";
                $output .= "    <td class=\"sht24-nowrap sht24-text-center\"><span class=\"" . sht24termine_getLevelClass($value["available_space"]) . "\">" . $value["available_space"] . ((get_option('show_capacity') == 1 && $value["total_spaces"] > 0) ? " / " . $value["total_spaces"] : "") . "</span></td>";
                if(get_option('show_price') == 1) $output .= "    <td class=\"sht24-nowrap\">" . number_format($value["price_per_person"], 2, ",", ".") . " &euro;</td>";
                $output .= "    <td class=\"sht24-nowrap\">";
                $output .= "     <a href=\"" . $value["training_details_url"] . "\" data-title=\"Trainingsdetails ansehen auf sicherheitstraining24.de\" class=\"sht24-info-btn sht24-tooltip\" target=\"_blank\"><i class=\"sht24-i-info\"></i></a> ";
                $output .= "     <a href=\"" . $value["ground_url"] . "\" data-title=\"" . $value["ground_name"] . "\" class=\"sht24-location-btn sht24-tooltip\" target=\"_blank\"><i class=\"sht24-i-map-marker\"></i></a> ";
                $output .= "     <a href=\"" . $value["training_booking_url"] . "\" data-title=\"Jetzt auf sicherheitstraining24.de buchen\" class=\"sht24-booking-btn sht24-tooltip " . (($value["available_space"] == 0) ? "sht24-d-none" : "") . "\" target=\"_blank\"><i class=\"sht24-i-booking\"></i></a>";
                $output .= "    </td>";
                $output .= "   </tr>";
                
            } 
        }
    }
    
    /* give out table footer with message below */
    $output .= "  </tbody>";
    
    $output .= "  <tfoot>";
    //$output .= $outputTfoot;
    $output .= "   <tr>";
    $output .= "    <td data-priority=\"1\" colspan=\"" . $colspan . "\" class=\"sht24-notice\">" . $message_below . "</td>";
    $output .= "   </tr>";
    $output .= "  </tfoot>";
    
    $output .= " </table>";
    $output .= "</div>";
    
    /* return the output variable */
    return $output;
}
