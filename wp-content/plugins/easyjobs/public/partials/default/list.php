<?php
/**
 * Render job list for shortcode
 *
 * @since 1.0.0
 */

?>

<?php if ( ! empty( $jobs ) && ! empty( $job_with_page_id ) ) : ?>
	<div class="easyjobs-shortcode-wrapper">
        <div class="ej-section">
		    <?php if ( ! empty( $company->ejel_enabled ) && ! empty( $company->ejel_job_list_enabled ) ) : ?>
                <div class="d-flex ej-job-filter-wrap">
                    <div class="ej-section-title">
						<?php if ( empty( $company->ejel_hide_job_list_heading_icon ) ) : ?>
                            <span class="ej-section-title-icon">
                                <?php if( ! empty( $company->ejel_joblist_heading_icon ) ) : ?>
                                <?php printf( '%s', $company->ejel_joblist_heading_icon ); // XSS : ok ?>
                                <?php else : ?>
                                <i class="easyjobs-icon easyjobs-briefcase"></i>                            
                                <?php endif; ?>
                            </span>
						<?php endif; ?>

						<?php if ( empty( $company->ejel_hide_job_list_heading_text ) ) : ?>
                            <span class="ej-section-title-text">
					<?php
					if ( empty ( $company->ejel_joblist_heading ) ) {
						echo esc_html(
							get_theme_mod(
								'easyjobs_landing_job_list_heading',
								esc_html__( 'Open Job Positions', 'easyjobs' )
							)
						);
					} else {
						esc_html_e( $company->ejel_joblist_heading, 'easyjobs' );
					}
					?>
				</span>
						<?php endif; ?>

                    </div>
					<?php
                        if ($company->show_job_filter) {
                            Easyjobs_Helper::job_filter( $jobs );
                        }
                    ?>
                </div>
            <?php else: ?>
                <div class="d-flex ej-job-filter-wrap">
                    <div class="ej-section-title">
                        <span class="ej-section-title-icon"><i class="easyjobs-icon easyjobs-briefcase"></i></span>
                        <span class="ej-section-title-text">
                                <?php
                                if ( empty ( $company->ejel_joblist_heading ) ) {
                                    echo esc_html(
                                        get_theme_mod(
                                            'easyjobs_landing_job_list_heading',
                                            esc_html__( 'Open Job Positions', 'easyjobs' )
                                        )
                                    );
                                } else {
                                    esc_html_e( $company->ejel_joblist_heading, 'easyjobs' );
                                }
                                ?>
                            </span>
                    </div>
                    <?php
                        if ($company->show_job_filter) {
                            Easyjobs_Helper::job_filter( $jobs );
                        }
                    ?>
                </div>
            <?php endif;?>
            <div class="ej-section-content">
                <div class="ej-job-list">
                    <?php foreach ( $jobs as $job ) : ?>
                        <?php
                        if ( empty( $company->ejel_enabled ) && $job->is_expired ) {
                            continue;
                        } else {
                            // Elementor Integration
                            if( $job->is_expired && 'yes' === $settings['easyjobs_show_open_job'] ) {
                                continue;
                            }
                        }
                        ?>
                        <div class="ej-job-list-item ej-job-list-item-cat" data-category="<?php echo esc_html($job->category->id)?>">
                            <div class="ej-job-list-item-inner">
                                <div class="ej-job-list-item-col">
                                    <h2 class="ej-job-title">
                                        <a href="<?php echo esc_url( get_the_permalink( $job_with_page_id[ $job->id ] ) ); ?>"><?php echo esc_html( $job->title ); ?></a>
                                    </h2>
                                    <?php if ( ! get_theme_mod( 'easyjobs_landing_hide_job_metas', false ) ) : ?>
                                        <div class="ej-job-list-info">
                                            <div class="ej-job-list-info-block ej-job-list-company-name">
                                                <i class="easyjobs-icon easyjobs-briefcase-2"></i>
                                                <a href="<?php echo esc_url( $job->company_easyjob_url ); ?>" target="_blank">
                                                    <?php echo esc_html( $job->company_name ); ?>
                                                </a>
                                            </div>
                                            <div class="ej-job-list-info-block ej-job-list-location">
                                                <i class="easyjobs-icon easyjobs-map-maker"></i>
                                                <?php if ( $job->is_remote === true || $job->is_remote === 'true' || $job->is_remote === 1 ) : ?>
                                                    <span>
                                                        <?php esc_html_e( 'Anywhere (Remote)', 'easyjobs' ); ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span>
                                                        <?php if ( ! empty( $job->job_address->city ) ) : ?>
                                                            <?php echo esc_html( $job->job_address->city->name ); ?>
                                                        <?php endif; ?>
                                                        <?php if ( ! empty( $job->job_address->country ) ) : ?>
                                                            , <?php echo esc_html( $job->job_address->country->name ); ?>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endif ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ej-job-list-item-col ej-job-time-col">
                                    <?php
                                    if ( ! $job->is_expired ) :
                                        ?>
                                        <p class="ej-deadline">
                                            <?php echo esc_html( $job->expire_at ); ?>
                                        </p>
                                        <p class="ej-list-sub">
                                            <?php esc_html_e( 'No of vacancies: ', 'easyjobs' ); ?> <?php echo $job->vacancies ? esc_html( $job->vacancies ) : '1'; ?>
                                        </p>
                                    <?php else : ?>
                                        <p class="ej-list-title ej-expired">
                                            <?php esc_html_e( 'Expired', 'easyjobs' ); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="ej-job-list-item-col ej-job-apply-btn">
                                    <a href="<?php echo ! empty( $job->apply_url ) ? esc_url( $job->apply_url ) : '#'; ?>" class="ej-btn ej-info-btn-light" target="_blank">
                                        <?php empty( $company->ejel_apply_button_text ) ? esc_html_e( 'Apply', 'easyjobs' ) : esc_html_e( $company->ejel_apply_button_text ); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
	</div>
<?php else : ?>
	<h3>
		<?php esc_html_e( 'No open jobs right now', 'easyjobs' ); ?>
	</h3>
<?php endif; ?>
