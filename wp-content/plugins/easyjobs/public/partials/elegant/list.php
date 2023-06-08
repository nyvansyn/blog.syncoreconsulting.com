<?php
/**
 * Render job list for shortcode
 *
 * @since 1.0.0
 */

?>

<?php if ( ! empty( $jobs ) && ! empty( $job_with_page_id ) ) : ?>
	<div class="easyjobs-shortcode-wrapper ej-template-elegant">
		<div class="ej-section">
            <?php if ( ! empty( $company->ejel_enabled ) && ! empty( $company->ejel_job_list_enabled ) ) : ?>
                <div class="section__header section__header--flex ej-job-filter-wrap">
                    <div class="ej-section-title">
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
            <div class="section__header section__header--flex ej-job-filter-wrap" id="open_job_position">
                <div class="ej-section-title">
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
            <?php endif; ?>
		</div>

        <div class="ej-job-list ej-job-list-elegant">
            <div class="ej-row">
                <?php foreach ( $jobs as $job ) : ?>
				<?php
                    if ( empty( $company->ejel_enabled ) && $job->is_expired ) {
                        continue;
                    } else {
                        // Elementor Integration
                        if( is_object( $job ) && $job->is_expired && 'yes' === $settings['easyjobs_show_open_job'] ) {
                            continue;
                        }
                    }
				?>
                <div class="ej-col-lg-6 ej-job-list-item-cat" data-category="<?php echo esc_html($job->category->id)?>">
                    <div class="job__card">
                        <h3 class="ej-job-title">
                            <a href="<?php echo esc_url( get_the_permalink( $job_with_page_id[ $job->id ] ) ); ?>"
                            >
								<?php echo esc_html( $job->title ); ?>
                            </a>
                        </h3>
                        <p class="meta">
                            <a href="<?php echo esc_url( $job->company_easyjob_url ); ?>" class="office__name">
                                <i class="easyjobs-icon easyjobs-briefcase"></i>
                                <?php echo is_object( $job ) ? esc_html( $job->company_name ) : ''; ?>
                            </a>

                            <span class="office__location">
                                <i class="easyjobs-icon easyjobs-map-maker"></i>
                                <?php if ( is_object( $job ) && $job->is_remote ) : ?>
                                    <span><?php esc_html_e( 'Anywhere (Remote)', 'easyjobs' ); ?></span>
								<?php else : ?>
                                    <span>
                                        <?php if(!empty($job->job_address->city) || !empty($job->job_address->country)): ?>
                                        <?php if ( ! empty( $job->job_address->city ) ) : ?>
                                            <?php echo esc_html( $job->job_address->city->name ); ?>
                                        <?php endif; ?>
                                        <?php if ( ! empty( $job->job_address->country ) ) : ?>
                                            , <?php echo esc_html( $job->job_address->country->name ); ?>
                                        <?php endif; ?>
                                        <?php else: ?>
                                            <?php echo esc_attr('N/A'); ?>
										<?php endif; ?>
                                    </span>
								<?php endif ?>
                            </span>
                        </p>
                        <div class="job__bottom">
                            <div class="job__apply">
                                <a href="<?php echo ! empty( $job->apply_url ) ? esc_url( $job->apply_url ) : '#'; ?>" class="button button__primary radius-15" target="_blank">
							        <?php empty( $company->ejel_apply_button_text ) ? esc_html_e( 'Apply now', 'easyjobs' ) : esc_html_e( $company->ejel_apply_button_text ); ?>
                                </a>
                            </div>
                            <div class="job__vacancy">
                                <h4><?php echo is_object( $job ) && $job->vacancies ? esc_html( $job->vacancies ) : 'N/A'; ?></h4>
                                <p><?php esc_html_e( 'No of vacancies', 'easyjobs' ); ?></p>
                            </div>
                        </div>
                        <span class="deadline"> <i class="ej-icon ej-calender"></i> <?php echo is_object( $job ) ? esc_html( $job->expire_at ) : ''; ?></span>
                    </div>
                </div>

                <?php endforeach; ?>
            </div>
		</div>
	</div>
<?php else : ?>
	<h3>
		<?php esc_html_e( 'No open jobs right now', 'easyjobs' ); ?>
	</h3>
<?php endif; ?>
