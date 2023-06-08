<?php

/**
 * This class is responsible for all job functionality in admin area
 *
 * @since 1.0.0
 */
class Easyjobs_Admin_Jobs {

    public $job_with_page = array();

    /**
     * Easyjobs_Admin_Jobs constructor.
     *
     * @since 1.0.5
     */
    public function __construct() {
		add_action( 'wp_ajax_easyjobs_search_jobs', array( $this, 'get_search_results' ) );
        add_action( 'wp_ajax_easyjobs_get_job_create_meta', array( $this, 'get_job_create_meta' ) );
        add_action( 'wp_ajax_easyjobs_save_job_information', array( $this, 'save_job_information' ) );
        add_action( 'wp_ajax_easyjobs_get_screening_question_meta', array( $this, 'get_screening_question_meta' ) );
        add_action( 'wp_ajax_easyjobs_save_screening_questions', array( $this, 'save_screening_questions' ) );
        add_action( 'wp_ajax_easyjobs_get_quiz_meta', array( $this, 'get_quiz_meta' ) );
        add_action( 'wp_ajax_easyjobs_save_quiz', array( $this, 'save_quiz' ) );
        add_action( 'wp_ajax_easyjobs_change_job_status', array( $this, 'change_job_status' ) );
        add_action( 'wp_ajax_easyjobs_get_job_data', array( $this, 'get_job_data' ) );
        add_action( 'wp_ajax_easyjobs_save_required_fields', array( $this, 'save_required_fields' ) );
        add_action( 'wp_ajax_easyjobs_delete_job', array( $this, 'delete_job' ) );
        add_action( 'wp_ajax_easyjobs_get_initial_job_templates', array( $this, 'get_initial_job_templates' ) );
        add_action( 'wp_ajax_easyjobs_duplicate_job', array( $this, 'duplicate_job' ) );
        add_action( 'wp_ajax_easyjobs_get_jobs', array( $this, 'get_jobs' ) );
    }
    /**
     * Show jobs
     *
     * @since 1.0.0
     * @return void
     */

    public function show_all() {
         $jobs = (object) array(
			 'published' => $this->get_published_jobs(),
			 'draft'     => $this->get_draft_jobs(),
			 'archived'  => $this->get_archived_jobs(),
		 );

		 $job_with_page_id       = Easyjobs_Helper::get_job_with_page( $jobs->published );
		 $new_job_with_page_id   = Easyjobs_Helper::create_pages_if_required( $jobs->published, $job_with_page_id );
		 $published_job_page_ids = $job_with_page_id + $new_job_with_page_id;

		 include EASYJOBS_ADMIN_DIR_PATH . 'partials/easyjobs-jobs-display.php';
    }

	public function get_jobs()
	{
		if(!Easyjobs_Helper::verified_request($_POST)){
			echo wp_json_encode(Easyjobs_Helper::get_error_response('Bad request'));
			wp_die();
		}
		$job_type = isset($_POST['job_type']) ? sanitize_text_field($_POST['job_type']) : 'published';

		switch ($job_type){
			case 'archived':
				$jobs = $this->get_archived_jobs($_POST['page'], $_POST['rows']);
				break;
			case 'draft':
				$jobs = $this->get_draft_jobs($_POST['page']);
				break;
			default:
				$jobs = $this->get_published_jobs(
					array_merge([
						'orderby' => 'expire_at', 'order' => 'desc'
					], Easyjobs_Helper::get_allowed_params_from_request($_POST)), $_POST['page']
				);
				$job_with_page_id       = Easyjobs_Helper::get_job_with_page( $jobs->data );
				$new_job_with_page_id   = Easyjobs_Helper::create_pages_if_required( $jobs->data, $job_with_page_id );
				$published_job_page_ids = $job_with_page_id + $new_job_with_page_id;
				foreach ($jobs->data as $job){
					$job->view_url = esc_url(get_the_permalink($published_job_page_ids[$job->id]));
				}
		}
		if(!empty($jobs)){
			echo wp_json_encode(Easyjobs_Helper::get_success_response('', $jobs));
		}else{
			echo wp_json_encode(Easyjobs_Helper::get_error_response('Unable to get jobs'));
		}
		wp_die();
	}

    /**
     * Get published jobs
     *
     * @since 1.0.0
     * @return object|bool
     */
    public function get_published_jobs($args = [], $page=1) {
		$jobs = Easyjobs_Api::get( 'published_jobs', array_merge( $args, ['page'=>$page] ) );
        if ( $jobs && $jobs->status == 'success' ) {
            return $jobs->data;
        }
        return false;
    }

    /**
     * Get draft jobs
     *
     * @since 1.0.0
     * @return object|bool
     */
    public function get_draft_jobs($page=1) {
         $jobs = Easyjobs_Api::get( 'draft_jobs', array('page' => $page) );
        if ( $jobs && $jobs->status == 'success' ) {
            return $jobs->data;
        }
        return false;
    }

    /**
     * Get archived jobs from api
     *
     * @since 1.0.0
     * @return object|bool
     */
    public function get_archived_jobs($page=1, $rows=10) {
         $jobs = Easyjobs_Api::get( 'archived_jobs', array('page' => $page, 'rows' => $rows) );
        if ( $jobs && $jobs->status == 'success' ) {
            return $jobs->data;
        }
        return false;
    }

    /**
     * Show search result
     *
     * @since 1.0.0
     */
    public function get_search_results() {
		if ( ! isset( $_POST['keyword'] ) && ! isset( $_POST['job_type'] ) && ! isset($_POST['page']) ) {
            return;
		};
        $type           = sanitize_text_field( $_POST['job_type'] );
        $job_page_links = array();
		if ( $type == 'published' ) {
			$result               = $this->search_results( 'published_jobs', array_merge(Easyjobs_Helper::get_allowed_params_from_request($_POST), ['orderby' => 'expire_at', 'order' => 'desc']) );

			$job_with_page_id     = Easyjobs_Helper::get_job_with_page( $result->data );
			// $new_job_with_page_id = Easyjobs_Helper::create_pages_if_required( $result->data, $job_with_page_id );
			// $job_page_ids         = $job_with_page_id + $new_job_with_page_id;
			foreach ( $result->data as $r ) {
				$r->view_url = get_permalink( $job_with_page_id[ $r->id ] );
			}
		}
		if ( $type == 'draft' ) {
			$result = $this->search_results( 'draft_jobs', Easyjobs_Helper::get_allowed_params_from_request($_POST) );
		}
		if ( $type == 'archived' ) {
			$result = $this->search_results( 'archived_jobs', Easyjobs_Helper::get_allowed_params_from_request($_POST) );
		}

		if ( ! empty( $result ) ) {
			echo wp_json_encode(
                array(
					'status'         => 'success',
					'jobs'           => $result,
					'job_page_links' => $job_page_links,
                )
            );
			wp_die();
		} else {
			echo wp_json_encode(
                array(
					'status' => 'error',
                )
			);
			wp_die();
		}
    }

    /**
     * Get search result from api
     *
     * @since 1.0.0
     * @param string $type
     * @param string $keyword
     * @return object|bool
     */
    public function search_results( $type, $args ) {
        $jobs = Easyjobs_Api::get( $type, array_merge([
			'page'=>1, 'rows'=>10
		], $args));
        if ( $jobs && $jobs->status == 'success' ) {
            return $jobs->data;
        }
        return false;
    }

    public function create_job() {
		wp_enqueue_script( 'easyjobs-react' );
        include EASYJOBS_ADMIN_DIR_PATH . '/partials/easyjobs-react-layout.php';
    }


    public function get_job_create_meta() {
         $metas = Easyjobs_Api::get( 'job_metas' );
        $data   = array();
        if ( Easyjobs_Helper::is_success_response( $metas->status ) ) {
            $data['meta'] = $metas->data;
        }
	    $data['company_info'] = Easyjobs_Helper::get_company_info();
        if ( ! empty( $data ) ) {
            echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $data,
                )
            );
        } else {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Unable to fetch all data required for job create.',
                )
            );
        }

        wp_die();
    }

    public function save_job_information() {
         $fields        = array(
			 'title',
			 'details',
			 'responsibilities',
			 'category',
			 'vacancies',
			 'is_remote',
             'job_type',
             'show_on_job_board',
			 'country',
			 'state',
			 'city',
			 'expire_at',
			 'employment_type',
			 'employment_type_other',
			 'experience_level',
			 'salary_type',
			 'salary',
			 'office_time',
			 'skills',
			 'benefits',
			 'has_benefits',
			 'coverPhoto',
			 'hideCoverPhoto',
		 );
		 $object_values = array(
			 'category',
			 'country',
			 'state',
			 'city',
			 'skills',
			 'employment_type',
			 'experience_level',
			 'salary_type',
             'job_type',
		 );
		 $data          = array();
		 foreach ( $this->sanitize_form_fields( $_POST, $fields ) as $key => $form_field ) {
			 if ( in_array( $key, $object_values ) ) {
				 $data[ $key ] = ! empty( $form_field ) ? json_decode( stripslashes( $form_field ) ) : null;
			 } else {
				 $data[ $key ] = $form_field;
			 }
		 }
		 if ( isset( $_POST['job_id'] ) ) {
			 $response = Easyjobs_Api::post( 'update_job_info', absint( sanitize_text_field($_POST['job_id']) ), $data );
		 } else {
			 $response = Easyjobs_Api::post( 'save_job_info', null, $data );
		 }
		 if ( Easyjobs_Helper::is_success_response( $response->status ) ) {
			 echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $response->data,
                )
			 );
		 } else {
			 echo wp_json_encode(
                array(
					'status' => 'error',
					'error'  => ! empty( $response->message ) ? Easyjobs_Helper::format_api_error_response( $response->message ) : array( 'global' => 'Something went wrong, please try again' ),
                )
			 );
		 }
		 wp_die();
    }

    public function get_screening_question_meta() {
         $meta = Easyjobs_Api::get( 'screening_question_meta' );
        if ( Easyjobs_Helper::is_success_response( $meta->status ) ) {
            echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $meta->data,
                )
            );
        } else {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => $meta->message,
                )
            );
        }
        wp_die();
    }

    public function save_screening_questions() {
		if ( ! isset( $_POST['job_id'] ) ) {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Job id not found',
                )
			);
			wp_die();
		}
		if ( ! isset( $_POST['questions'] ) ) {
			echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Questions not found',
                )
            );
			wp_die();
		}
        $questions = json_decode( wp_unslash( $_POST['questions'] ) );
        $job_id    = absint( sanitize_text_field($_POST['job_id']) );
        $sanitized = array();
		foreach ( $questions as $question ) {
			$sanitized[] = $this->sanitize_form_fields( $question, array( 'id', 'title', 'type', 'options', 'answers' ) );
		}
        $response = Easyjobs_Api::post( 'save_questions', $job_id, array( 'questions' => $sanitized ) );

		if ( Easyjobs_Helper::is_success_response( $response->status ) ) {
			echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $response->data,
                )
            );
		} else {
			echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => $response->message,
                )
			);
		}

        wp_die();

    }

    public function get_quiz_meta() {
         $meta = Easyjobs_Api::get( 'quiz_meta' );
        if ( $meta->status === 'success' ) {
            echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $meta->data,
                )
            );
        } else {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => $meta->message,
                )
            );
        }
        wp_die();
    }

    public function save_quiz() {
		if ( ! isset( $_POST['job_id'] ) ) {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Job id not found',
                )
			);
			wp_die();
		}
		if ( ! isset( $_POST['form_data'] ) ) {
			echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'No data to save',
                )
            );
			wp_die();
		}
        $form_data = json_decode( wp_unslash( $_POST['form_data'] ) );
        $questions = $form_data->questions;
        $job_id    = absint( sanitize_text_field($_POST['job_id']) );
        $sanitized = array();
		foreach ( $questions as $question ) {
			$sanitized[] = $this->sanitize_form_fields( $question, array( 'id', 'title', 'type', 'options', 'answers' ) );
		}

        $response = Easyjobs_Api::post(
            'save_quiz',
            $job_id,
            array(
				'questions'          => $sanitized,
				'exam_duration'      => sanitize_text_field( $form_data->exam_duration ),
				'marks_per_question' => sanitize_text_field( $form_data->marks_per_question ),
			)
        );

        if ( Easyjobs_Helper::is_success_response( $response->status ) ) {
            echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $response->data,
                )
            );
        } else {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => $response->message,
                )
            );
        }
        wp_die();

    }

    public function change_job_status() {
		if ( ! isset( $_POST['job_id'] ) ) {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Job id not found',
                )
			);
			wp_die();
		}
		if ( ! isset( $_POST['status'] ) ) {
			echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Status not provided',
                )
            );
			wp_die();
		}
        $status = json_decode( wp_unslash( sanitize_text_field($_POST['status']) ), true);
        
        $company = Easyjobs_Helper::get_company_info(true);
		if ( ! empty( $company ) && $company->stats->published_jobs >= 1 ) {
			if ( ! $company->is_pro || ( $company->subscription_expired && absint( sanitize_text_field($status['status']) ) == 2 ) ) {
				echo wp_json_encode(
                    array(
						'status'  => 'error',
						'message' => 'Your subscription is expired, you can not publish more than one job',
                    )
                );
				wp_die();
			}
		}
        $requestData = [
            'status' => absint( sanitize_text_field($status['status']) )
        ];
        if(isset($status['change_expire_date']) && !empty($status['change_expire_date'])){
            $requestData['change_expire_date'] = $status['change_expire_date'];
        }
        if(isset($status['expire_date_status']) && !empty($status['expire_date_status'])){
            $requestData['expire_date_status'] = absint( sanitize_text_field($status['expire_date_status']) );
        }

        if ($requestData['expire_date_status'] == 1) {
            $message = 'Job re-published';
        } else if($requestData['expire_date_status'] == 2) {
            $message = 'Job expired';
        }

        $response = Easyjobs_Api::post(
            'change_status',
            absint( sanitize_text_field($_POST['job_id']) ),
            $requestData
        );

        if ( Easyjobs_Helper::is_success_response( $response->status ) ) {
            echo wp_json_encode(
                array(
					'status' => 'success',
					'data'   => $response->data,
                    'message'=> $message,
                )
            );
        } else {
            echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => $response->message,
                )
            );
        }
        wp_die();
    }

    public function get_job_data() {
		if ( ! isset( $_POST['job_id'] ) ) {
            echo wp_json_encode(Easyjobs_Helper::get_error_response( 'Job id not provided' ));
            wp_die();
		}
		if ( ! isset( $_POST['type'] ) ) {
			echo wp_json_encode(Easyjobs_Helper::get_error_response( 'No type provided' ));
			wp_die();
		}

        echo wp_json_encode(Easyjobs_Helper::get_generic_response(
			Easyjobs_Api::get_by_id(
				'job',
				absint( $_POST['job_id'] ),
				sanitize_text_field( $_POST['type'] )
			)
		));

        wp_die();
    }

    public function save_required_fields() {
        if ( ! isset( $_POST['job_id'] ) ) {
            echo wp_json_encode(Easyjobs_Helper::get_error_response( 'Job id not provided' ));
            wp_die();
        }
        if ( ! isset( $_POST['data'] ) ) {
            echo wp_json_encode(Easyjobs_Helper::get_error_response( 'No data provided' ));
            wp_die();
        }
        echo wp_json_encode(Easyjobs_Helper::get_generic_response(
			Easyjobs_Api::post(
				'required_fields',
				absint( sanitize_text_field($_POST['job_id']) ),
				(array) json_decode( wp_unslash( $_POST['data'] ) )
			)
		));

        wp_die();
    }

    public function delete_job() {
		if ( ! Easyjobs_Helper::verified_request($_POST) ) {
            echo wp_json_encode(Easyjobs_Helper::get_error_response('Invalid request'));
			wp_die();
		}
		if ( ! isset( $_POST['form_data'] ) && ! isset( $_POST['job_id'] ) ) {
			echo wp_json_encode(
                array(
					'status'  => 'error',
					'message' => 'Empty form data or job id',
                )
            );
			wp_die();
		}
        $response = Easyjobs_Api::post( 'delete_job', absint( sanitize_text_field($_POST['job_id']) ), array() );
		if ( Easyjobs_Helper::is_success_response( $response->status ) ) {
			$this->delete_job_page( absint( $_POST['job_id'] ) );
			echo wp_json_encode(Easyjobs_Helper::get_success_response( __( 'Job deleted successfully', 'easyjobs' ) ));
		} else {
			echo wp_json_encode(Easyjobs_Helper::get_error_response( __( 'Failed to delete job, please try again or contact support', 'easyjobs' ) ));
		}

        wp_die();
    }

    public function get_initial_job_templates() {
         $response_data = array();
        if ( isset( $_POST['industry_id'] ) && ! empty( $_POST['industry_id'] ) ) {
			$industry = sanitize_text_field($_POST['industry_id']);
            if ( trim( $industry ) == 'all' ) {
                $industry = '';
            } else {
                $industry = abs( $industry );
            }
        } else {
            $company = Easyjobs_Helper::get_company_info();
            if ( empty( $company->industry ) ) {
                $company_info = Easyjobs_Api::get( 'company_info' );
                if ( Easyjobs_Helper::is_success_response( $company_info->status ) ) {
                    $company = $company_info->data;
                    update_option( 'easyjobs_company_info', serialize( $company ) );
                }
            }
            $industry                 = $company->industry->id;
            $response_data['company'] = $company;
        }
        $initial_templates = Easyjobs_Api::get(
            'job_templates',
            array(
				'industry_id' => $industry,
				'title'       => sanitize_text_field( $_POST['title'] ),
				'page'        => absint( sanitize_text_field($_POST['page']) ),
			)
        );
        if ( Easyjobs_Helper::is_success_response( $initial_templates->status ) ) {
            $response_data['templates'] = $initial_templates->data;
            echo wp_json_encode(Easyjobs_Helper::get_success_response( 'Successfully get templates', $response_data ));
        } else {
            echo wp_json_encode(Easyjobs_Helper::get_error_response( 'Unable to get job templates, please try again' ));
        }
        wp_die();
    }

	public function duplicate_job()
	{
		if ( ! Easyjobs_Helper::verified_request($_POST) ) {
			echo wp_json_encode(Easyjobs_Helper::get_error_response('Invalid request'));
			wp_die();
		}
		if ( empty( $_POST['job_id'] )) {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => 'Job not provided',
				)
			);
			wp_die();
		}
		echo wp_json_encode(
			Easyjobs_Helper::get_generic_response(
				Easyjobs_Api::post(
					'job_duplicate',
					sanitize_text_field($_POST['job_id'])
				)
			)
		);
		wp_die();
	}

    private function sanitize_form_fields( $post_data, $fields ) {
        $data          = array();
        $editor_fields = array( 'details', 'responsibilities' );
        $checkboxes = array( 'is_remote', 'hideCoverPhoto' );
        foreach ( $post_data as $key => $value ) {
            if ( in_array( $key, $fields ) ) {
                if ( Easyjobs_Helper::is_iterable( $value ) ) {
                    $data[ sanitize_text_field( $key ) ] = $value;
                } else {
                    if ( $key === 'id' ) {
                        if ( ! empty( $value ) ) {
                            $data[ sanitize_text_field( $key ) ] = absint( $value );
                        } else {
                            $data[ sanitize_text_field( $key ) ] = null;
                        }
					} else {
                        if ( in_array( $key, $editor_fields ) ) {
                            $data[ sanitize_text_field( $key ) ] = wp_kses_post( $value );
						} else {
							if(in_array($key, $checkboxes)){
								$data[ sanitize_text_field( $key ) ] = $value == 1 ? 1 : 0;
							}else{
								$data[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
							}

						}
					}
                }
			}
        }
        return $data;
    }

    private function delete_job_page( $job_id ) {
        $pages = get_posts(
            array(
				'post_type'      => 'page',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					array(
						'key'     => 'easyjobs_job_id',
						'value'   => $job_id,
						'compare' => 'IN',
					),
				),
            )
        );
        foreach ( $pages as $page ) {
            wp_delete_post( $page->ID, true );
        }
        return $pages;
    }
}
