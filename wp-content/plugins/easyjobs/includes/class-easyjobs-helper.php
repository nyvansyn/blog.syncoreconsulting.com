<?php

/**
 * Class Easyjobs_Helper
 */
class Easyjobs_Helper {
    /**
     * @param $rating
     * @return void
     */
    public static function rating_icon( $rating ) {
        for ( $i = 1; $i <= 5; $i++ ) {
            ?>
            <div class="rate-count <?php echo $i <= $rating ? esc_html( 'rated has-rating' ) : ''; ?>">
                <i class="easyjobs-icon easyjobs-star"></i>
            </div>
            <?php
        }
    }

    /**
     * Pagination helper for candidates
     *
     * @param array $params
	 * @return array
	 */
    public static function paginate( array $params ) {
        extract($params);
        if ( !isset($current) || !isset($max) ) {
            return null;
        }
        $prev = $current === 1 ? null : $current - 1;
        $next = $current === $max ? null : $current + 1;
        $items = [1];
    
        if ($current === 1 && $max === 1) {
            return [
                "current"   => $current,
                "prev"      => $prev,
                "next"      => $next,
                "items"     => $items,
            ];
        }
        if ($current > 4) {
            array_push($items, "...");
        }
        $r  = 2;
        $r1 = $current - $r;
        $r2 = $current + $r;
    
        for ($i = $r1 > 2 ? $r1 : 2; $i <= min($max, $r2); $i++) {
            array_push($items, $i);
        }

        if ($r2 + 1 < $max) {
            array_push($items, "...");
        }
        if ($r2 < $max) {
            array_push($items, $max);
        }
    
        return [
            "current"   => $current,
            "prev"      => $prev,
            "next"      => $next,
            "items"     => $items,
        ];
    }

    /**
     * This function is responsible for making an array sort by their key
     *
     * @param  array  $data
     * @param  string $using
     * @param  string $way
     * @return array
     */
    public static function sorter( $data, $using = 'time_date', $way = 'DESC' ) {
        if ( ! is_array( $data ) ) {
            return $data;
        }
        $new_array = array();
        if ( $using === 'key' ) {
            if ( $way !== 'ASC' ) {
                krsort( $data );
            } else {
                ksort( $data );
            }
        } else {
            foreach ( $data as $key => $value ) {
                if ( ! is_array( $value ) ) {
                    continue;
                }
                foreach ( $value as $inner_key => $single ) {
                    if ( $inner_key == $using ) {
                        $value['tempid']      = $key;
                        $single               = self::numeric_key_gen( $new_array, $single );
                        $new_array[ $single ] = $value;
                    }
                }
            }

            if ( $way !== 'ASC' ) {
                krsort( $new_array );
            } else {
                ksort( $new_array );
            }

            if ( ! empty( $new_array ) ) {
                foreach ( $new_array as $array ) {
                    $index = $array['tempid'];
                    unset( $array['tempid'] );
                    $new_data[ $index ] = $array;
                }
                $data = $new_data;
            }
        }

        return $data;
    }

    /**
     * This function is responsible for generate unique numeric key for a given array.
     *
     * @param  array   $data
     * @param  integer $index
     * @return integer
     */
    private static function numeric_key_gen( $data, $index = 0 ) {
        if ( isset( $data[ $index ] ) ) {
            $index += 1;
            return self::numeric_key_gen( $data, $index );
        }
        return $index;
    }

    /**
     * This function is responsible for the data sanitization
     *
     * @param  array        $field
     * @param  string|array $value
     * @return string|array
     */
    public static function sanitize_field( $field, $value ) {
        if ( isset( $field['sanitize'] ) && ! empty( $field['sanitize'] ) ) {
            if ( function_exists( $field['sanitize'] ) ) {
                $value = call_user_func( $field['sanitize'], $value );
            }
            return $value;
        }

        if ( is_array( $field ) && isset( $field['type'] ) ) {
            switch ( $field['type'] ) {
                case 'text':
                    $value = sanitize_text_field( $value );
                    break;
                case 'textarea':
                    $value = sanitize_textarea_field( $value );
                    break;
                case 'email':
                    $value = sanitize_email( $value );
                    break;
                default:
                    return $value;
            }
        } else {
            $value = sanitize_text_field( $value );
        }

        return $value;
    }

    /**
     * Get single job from api
     *
     * @since 1.0.0
     * @param $job_id
     * @return object|bool
     */
    public static function get_job( $job_id ) {
        $job = Easyjobs_Api::get_by_id( 'job', $job_id, 'details' );
        if ( $job && $job->status == 'success' ) {
            return $job->data;
        }
        return false;
    }

    /**
     * @return string[][]
     */
    public static function subscription_constants() {
        return array(
            'plan' => array(
                1 => 'Month',
                2 => 'Year',
                3 => 'Lifetime',
            ),
            'type' => array(
                1  => 'Regular',
                50 => 'App Sumo',
                2  => 'Special',
            ),
        );
    }

    /**
     * @return bool
     */
    public static function is_api_connected() {
        $settings = EasyJobs_DB::get_settings();
        if ( isset( $settings['easyjobs_api_key'] ) && ! empty( $settings['easyjobs_api_key'] ) ) {
            return true;
        }
        return false;
    }

    /**
     * @param $employment_type
     * @return string
     */
    public static function get_employment_badge( $employment_type ) {
        if ( strtolower( $employment_type ) == 'permanent' ) {
            return '<span class="ej-employment-badge permanent">Permanent</span>';
        }
        return '<span class="ej-employment-badge ' . strtolower( $employment_type ) . '">' . $employment_type . '</span>';
    }

    /**
     * @param $number
     * @param $text
     * @return string
     */
    public static function get_dynamic_label( $number, $text ) {
        $labels      = array(
            'info-label',
            'success-label',
            'primary-label',
            'danger-label',
            'warning-label',
        );
        $label_count = count( $labels );
        if ( $number < $label_count ) {
            return '<span class="ej-label ej-' . $labels[ $number ] . '">' . $text . '</span>';
        } else {
            $number   = $number + 1;
            $position = ( $number - ( floor( $number / $label_count ) * $label_count ) ) - 1;
            return '<span class="ej-label ej-' . $labels[ $position ] . '">' . $text . '</span>';
        }
    }

	/**
	 * Render array in job_id => page_id format
     *
	 * @since 1.0.0
	 * @param object $jobs
	 * @return array
	 */
	public static function get_job_with_page( $jobs ) {
		$job_with_page = array();
		$pages         = self::get_job_pages( $jobs );
		if ( ! empty( $pages ) ) {
			foreach ( $pages as $page ) {
				$job_with_page[ get_post_meta( $page->ID, 'easyjobs_job_id', true ) ] = $page->ID;
			}
		}

		return $job_with_page;
	}

	/**
	 * Get pages that contains shortcode
     *
	 * @since 1.0.0
	 * @param object $jobs
	 * @return array|null
	 */
	public static function get_job_pages( $jobs ) {
		$published_jobs_ids = array();
		if ( empty( $jobs ) ) {
			return null;
		}
		foreach ( $jobs as $key => $job ) {
            if( is_object($job) ) {
                array_push( $published_jobs_ids, $job->id );
            }
		}
		array_push( $published_jobs_ids, 'all' );

		$pages = get_posts(
            array(
				'post_type'      => 'page',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => 'easyjobs_job_id',
						'value'   => $published_jobs_ids,
						'compare' => 'IN',
					)
				),
            )
        );
		return $pages;
	}

    /**
     * Get company info
     * @return object|false
     */
    public static function get_company_info( $cached = false ) {
        $company_info = get_option( 'easyjobs_company_info' );
        if ( ! $cached && ! empty( $company_info ) ) {
            return unserialize( $company_info );
        }
	    $settings = EasyJobs_DB::get_settings();
	    if ( ! empty( $settings['easyjobs_api_key'] ) ) {
		    $company_info = Easyjobs_Api::get( 'company_info' );
		    if ( self::is_success_response( $company_info->status ) ) {
			    update_option( 'easyjobs_company_info', serialize( $company_info->data ) );
			    return $company_info->data;
		    }
	    }
        return false;
    }

    /**
     * @param $type
     * @return string
     */
    public static function get_social_link_icon( $type ) {
        switch ( strtolower( $type ) ) {
            case 'facebook':
                $icon = '<i class="easyjobs-icon easyjobs-facebook"></i>';
                break;
            case 'linkedin':
                $icon = '<i class="easyjobs-icon easyjobs-linkedin"></i>';
                break;
            case 'twitter':
                $icon = '<i class="easyjobs-icon easyjobs-twitter"></i>';
                break;
            default:
                $icon = '<i class="dashicons dashicons-share"></i>';
                break;
        }
        return $icon;

    }

    /**
     * Get frontend landing page
     *
     * @since 1.0.4
     * @return int|null
     */
    public static function get_landing_page() {
		$landing_page_id = get_option( 'easyjobs_parent_page' );
        if ( ! empty( $landing_page_id ) ) {
            return $landing_page_id;
		} else {
			$page = get_posts(
				array(
					'post_type'      => 'page',
					'posts_per_page' => 1,
					'meta_query'     => array(
						array(
							'key'     => 'easyjobs_job_id',
							'value'   => 'all',
							'compare' => '=',
						),
					),
				)
			);
		}

        if ( ! empty( $page ) ) {
            return $page[0]->ID;
        }

        return null;
    }

    /**
     * @param $name
     * @return string|string[]
     */
    public static function get_tab_name( $name ) {
        return str_replace( ' ', '-', strtolower( $name ) );
    }

    /**
     * @param $status
     * @return string
     */
    public static function get_job_status_badge( $status ) {
        switch ( $status ) {
            case 1:
                ?>
                    <div class="thumbnail__status thumbnail__status--warning"> <?php esc_html_e( 'Draft', 'easyjobs' ); ?></div>
                <?php
                break;
            case 2:
				?>
                    <div class="thumbnail__status thumbnail__status--success"> <?php esc_html_e( 'Active', 'easyjobs' ); ?></div>
				<?php
				break;
            case 3:
				?>
                    <div class="thumbnail__status thumbnail__status--info"> <?php esc_html_e( 'Archived', 'easyjobs' ); ?></div>
				<?php
				break;
            case 4:
				?>
                    <div class="thumbnail__status thumbnail__status--danger"> <?php esc_html_e( 'Deleted', 'easyjobs' ); ?></div>
				<?php
				break;
            case 10:
				?>
                    <div class="thumbnail__status thumbnail__status--success"> <?php esc_html_e( 'Republished', 'easyjobs' ); ?></div>
				<?php
				break;
            default:
				?>
                <div class="thumbnail__status thumbnail__status--success"> <?php esc_html_e( 'Active', 'easyjobs' ); ?></div>
			    <?php

        }
    }

    /**
     * Create pages for jobs if not created
     *
     * @since 1.1.1
     * @param object $jobs
     * @param array  $job_with_page_id
     * @return array
     */

    public static function create_pages_if_required( $jobs, $job_with_page_id ) {
        $parent_page = null;
        if ( isset( $job_with_page_id['all'] ) && ! empty( $job_with_page_id['all'] ) ) {
            $parent_page = $job_with_page_id['all'];
        }
        $new_job_id_page_id = array();
        if ( empty( $parent_page ) ) {
            $parent_page = self::create_parent_page();
        }
        if ( ! empty( $parent_page ) && ! empty( $jobs ) ) {
            foreach ( $jobs as $key => $job ) {
                if( ! is_object( $job ) || empty($job->id)){
                    continue;
                }
                
                if ( array_key_exists( $job->id, $job_with_page_id ) ) {
                    continue;
                }
                $page_id = wp_insert_post(
                    array(
						'post_type'     => 'page',
						'post_title'    => $job->title,
						'post_status'   => 'publish',
						'post_parent'   => $parent_page,
						'post_content'  => '[easyjobs_details id=' . $job->id . ']',
						'page_template' => 'easyjobs-template.php',
                    )
                );
                if ( $page_id ) {
                    update_post_meta( $page_id, 'easyjobs_job_id', $job->id );
                    $new_job_id_page_id[ $job->id ] = $page_id;
                }
            }
        }
        return $new_job_id_page_id;
    }


    /**
     * @param $scores
     * @return string
     */
    public static function get_ai_score_circles( $scores ) {
        $html   = '';
        $scores = array_filter(
            (array) $scores,
            function( $s ) {
				return ! empty( $s );
			}
        );

        unset( $scores['final_score'] );

        $sum         = array_sum( $scores );
        $key         = 1;
        $prev_offset = 0;
        $prev_result = 0;
        foreach ( $scores as $name => $score ) {

            $result = (int) round( ( $score / $sum ) * 100 );

            if ( $key == 1 ) {
                $offset = $prev_offset;
            } else {
                $offset      = ( 100 - $prev_result ) + $prev_offset;
                $prev_offset = $offset;
            }

            $prev_result = $result;

			?>
            <circle class="donut-segment" cx="18" cy="18" r="18" fill="transparent" stroke="<?php echo self::get_ai_score_color( esc_attr( $name ) ); ?>" stroke-width="3" stroke-dasharray="<?php echo esc_attr( $result ); ?>, <?php echo ( 100 - esc_attr( $result ) ); ?>" stroke-dashoffset="<?php echo esc_attr( $offset ); ?>"></circle>
			<?php

            $key++;
        }

    }

    /**
     * @param $name
     * @return string
     */
    public static function get_ai_score_color( $name ) {
        $colors = array(
            'quiz'        => '#ff9635',
            'skill'       => '#2fc1e1',
            'education'   => '#597dfc',
            'experience'  => '#60ce83',
            'final_score' => '#ff5f74',
        );

        return $colors[ $name ];
    }

    /**
     * @param $scores
     */
    public static function get_ai_score_details( $scores ) {
        include EASYJOBS_ADMIN_DIR_PATH . 'partials/easyjobs-ai-score-details.php';
    }

    /**
     * @param $scores
     */
    public static function get_ai_score_chart( $scores ) {
        include EASYJOBS_ADMIN_DIR_PATH . 'partials/easyjobs-ai-score-chart.php';
    }

    /**
     * @param $total
     * @param $mark
     * @return float|int
     */
    public static function get_mark_percentage( $total, $mark ) {
        if ( empty( $total ) ) {
            return 0;
        }
        return round( ( $mark / $total ) * 100 );
    }


    /**
     * @param $status
     * @return bool
     */
    public static function is_success_response( $status ) {
        if ( strtolower( trim( $status ) ) === 'success' ) {
            return true;
        }

        return false;
    }

    /**
     * @param $name
     * @return string
     */
    public static function get_pipeline_label( $name ) {
        switch ( strtolower( $name ) ) {
            case 'selected':
                return 'success-label';
            case 'rejected':
                return 'danger-label';
            default:
                return 'primary-label';
        }
    }

    /**
     * Format api error response to display in frontend
     *
     * @param object $error_messages
     * @since 1.1.2
     * @return array
     */

    public static function format_api_error_response( $error_messages ) {
        $errors = array();
        if ( ! self::is_iterable( $error_messages ) ) {
            return array(
                'global' => $error_messages,
            );
        }
        foreach ( $error_messages as $key => $message ) {
            if ( is_array( $message ) ) {
                foreach ( $message as $k => $m ) {
                    if ( $k > 1 ) {
                        $errors[ $key ] .= ' and ';
                    }
                    $errors[ $key ] .= $m;

                }
            } else {
                $errors['global'] = ' ' . $message;
            }
        }
        return $errors;
    }

    /**
     * @param $var
     * @return bool
     */
    public static function is_iterable( $var ) {
        return is_array( $var ) || $var instanceof \Traversable || $var instanceof stdClass;
    }

    /**
     * @param $response
     * @return array
     */
    public static function get_generic_response( $response ) {
        if ( empty( $response->status ) ) {
            return self::get_error_response( $response );
        }
        if ( self::is_success_response( $response->status ) ) {
            return array(
                'status' => 'success',
                'data'   => $response->data,
            );
        } else {
            return self::get_error_response( $response->message );
        }
    }

    /**
     * @param $message
     * @return array
     */
    public static function get_error_response( $message, $errors=[] ) {
        return array(
            'status'  => 'error',
            'message' => $message,
            'errors' => $errors
        );
    }

    /**
     * @return bool|null
     */
    public static function get_verification_status() {
		if ( $status = get_transient( 'easyjobs_company_verification_status' ) ) {
            return trim( $status ) == 'yes';
		}
        $response = Easyjobs_Api::get( 'settings_basic_info' );
		if ( self::is_success_response( $response->status ) ) {
			set_transient( 'easyjobs_company_verification_status', $response->data->is_verified ? 'yes' : 'no', 3600 );
			return $response->data->is_verified;
		}
        return null;
    }

    /**
     * Candidate sorting options with values
     *
     * @since 1.3.1
     * @return array[]
     */
    public static function candidate_sort_options() {
         return array(
			 array(
				 'title' => 'Sort by experience',
				 'value' => 'SORT_BY_EXPERIENCE',
			 ),
			 array(
				 'title' => 'Sort by skill match',
				 'value' => 'SORT_BY_SKILL_MATCH',
			 ),
			 array(
				 'title' => 'Sort by education match',
				 'value' => 'SORT_BY_EDUCATION_MATCH',
			 ),
			 array(
				 'title' => 'Sort by experience match',
				 'value' => 'SORT_BY_EXPERIENCE_MATCH',
			 ),
			 array(
				 'title' => 'Sort by AI score',
				 'value' => 'SORT_BY_TOTAL_AI_SCORE',
			 ),
			 array(
				 'title' => 'Sort by quiz score',
				 'value' => 'SORT_BY_QUIZ_SCORE',
			 ),
		 );
    }

    /**
     * Check if ai setup is enabled and insert it in db if required
     *
     * @since 1.3.1
     * @return bool
     */
    public static function is_ai_enabled() {
         $exist = EasyJobs_DB::get_settings( 'easyjobs_ai_setup', true );
        if ( $exist ) {
            return $exist === 'yes';
        } else {
            $response = Easyjobs_Api::get( 'ai_setup' );
            if ( self::is_success_response( $response->status ) ) {
                EasyJobs_DB::update_settings( (bool) $response->data->ai_setup_enabled ? 'yes' : 'no', 'easyjobs_ai_setup' );
                return (bool) $response->data->ai_setup_enabled;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public static function customizer_link() {

        $query['autofocus[panel]'] = 'easyjobs_customize_options';
        $query['return']           = admin_url( rawurlencode( 'admin.php?page=easyjobs-settings' ) );

        $job_landing_page = self::get_landing_page();

        if ( ! empty( $job_landing_page ) ) {
            $query['url'] = get_permalink( $job_landing_page );
        }

        return add_query_arg( $query, admin_url( 'customize.php' ) );
    }

    public static function create_parent_page() {
        $parent = get_option( 'easyjobs_parent_page' );
        if ( ! $parent ) {
            $has_parent = get_posts(
                array(
					'post_type'      => 'page',
					'posts_per_page' => - 1,
					'meta_query'     => array(
						array(
							'key'     => 'easyjobs_job_id',
							'value'   => 'all',
							'compare' => '=',
						),
					),
                )
            );
            if ( ! empty( $has_parent ) ) {
                update_option( 'easyjobs_parent_page', $has_parent[0]->ID );
                return $has_parent[0]->ID;
            } else {
                $page_id = wp_insert_post(
                    array(
						'post_type'     => sanitize_text_field( 'page' ),
						'post_title'    => sanitize_text_field( 'Jobs' ),
						'post_status'   => sanitize_text_field( 'publish' ),
						'post_content'  => sanitize_textarea_field( '[easyjobs]' ),
						'page_template' => sanitize_file_name( 'easyjobs-template.php' ),
                    )
                );
                if ( $page_id ) {
                    update_post_meta( $page_id, 'easyjobs_job_id', 'all' );
                    update_option( 'easyjobs_parent_page', $page_id );
                    return $page_id;
                }
            }
        }

        return $parent;
    }

    /**
     * @return bool
     */
    public static function disconnect_plugin(){
		try {
			delete_transient( 'easyjobs_company_verification_status' );
			delete_option( 'easyjobs_ai_setup' );
			delete_option( 'easyjobs_analytics_connected' );
			delete_option( 'easyjobs_settings' );
			delete_option( 'easyjobs_company_info' );
			delete_option( 'easyjobs_parent_page' );
			return true;
		} catch ( Exception $e ) {
			return false;
		}
    }

	/**
	 * @param string $message
	 * @param mixed  $data
	 * @return array
	 */
	public static function get_success_response( $message, $data = array() ) {
        return array(
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        );
    }

	public static function verified_request($request)
	{
        return isset($request['nonce']) && wp_verify_nonce($request['nonce'],'easyjobs_react_nonce');
    }

	public static function check_verified_request()
	{
        if(isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'],'easyjobs_react_nonce')){
            return true;
		}
        echo wp_json_encode(self::get_error_response('Invalid request'));
        wp_die();
    }

	public static function get_path_by_template($template, $file)
	{
		$template = !empty($template) ? $template : 'default';
		$path = EASYJOBS_PUBLIC_PATH . 'partials/' . $template . '/' . $file . '.php';
		if(file_exists($path)){
			return $path;
		}
		return EASYJOBS_PUBLIC_PATH . 'partials/default/' . $file . '.php';
    }

	/**
	 * Job filter and search
	 * @param $jobs array
	 * @return false|string
	 */
	public static function job_filter($jobs){
		ob_start();
		?>
        <form class="ej-job-filter-form job-filter d-flex">
	        <?php if ( get_theme_mod('easyjobs_landing_hide_job_search_by_title') == '1'): ?>
	            <div class="search-bar">
	                <input type="text" name="job_title" value="" class="form-control" placeholder="Job Title">
	            </div>
			<?php endif; ?>
			<?php if ( get_theme_mod('easyjobs_landing_hide_job_search_by_category') == '1'): ?>
	            <div class="select-option">
	                <select name="job_category">
	                    <option value="all" selected>Select Category</option>
						<?php foreach (self::get_categories($jobs) as $category): ?>
	                        <option value="<?php echo esc_html($category['id'])?>"><?php echo esc_html($category['name']); ?></option>
						<?php endforeach;?>
	                </select>
	            </div>
			<?php endif; ?>
			<?php if ( get_theme_mod('easyjobs_landing_hide_job_search_by_title') == '1' || get_theme_mod('easyjobs_landing_hide_job_search_by_category') == '1'): ?>
	            <button class="ej-btn ej-info-btn-light" type="submit"><?php _e("Submit",'easyjobs');?></button>
	            <button id="ej-reset-form" class="ej-btn ej-danger-btn" type="reset"><?php _e("Reset",'easyjobs');?></button>
			<?php endif; ?>
        </form>
		<?php
		return ob_get_flush();
	}

	public static function get_allowed_params_from_request($request)
	{
        $params = ['rows','order','orderby', 'page', 'status','search'];
        $filtered = [];
        foreach ($params as $param){
            if(!empty($request[$param])){
                $filtered[$param] = sanitize_text_field($request[$param]);
			}
		}
        return $filtered;
    }

	private static function get_categories($jobs)
	{
		$categories = [];
		$inserted = [];
		foreach ($jobs as $job){
			if(!in_array($job->category->id, $inserted) && !$job->is_expired){
				$categories[] = [
					'id' => $job->category->id,
					'name' => $job->category->name
				];
				$inserted[] = $job->category->id;
			}

		}
		return $categories;
	}
}
