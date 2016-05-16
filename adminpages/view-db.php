<?php global $ucicurl_viewdb; ?>

<div class="uci-view-db">
	<h2>UCI View DB</h2>

	<div class="view-db-filter">
		<div class="races">
			<h3>Races</h3>

			<div class="tablenav top">
				<div class="alignleft actions">
					<select name="season" class="season">
						<option value="0">-- Select Season --</option>
						<?php foreach ($ucicurl_viewdb->seasons() as $season) : ?>
							<option value="<?php echo $season; ?>"><?php echo $season; ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="alignleft actions">
					<select name="class" class="class">
						<option value="0">-- Select Class --</option>
						<?php foreach ($ucicurl_viewdb->classes() as $class) : ?>
							<option value="<?php echo $class; ?>"><?php echo $class; ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="alignleft actions">
					<select name="nat" class="nat">
						<option value="0">-- Select Country --</option>
						<?php foreach ($ucicurl_viewdb->nats() as $country) : ?>
							<option value="<?php echo $country; ?>"><?php echo $country; ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<input type="submit" id="doaction" class="button action" value="Apply">

				<div class="alignright actions">
					<input id="race-search" type="text" />
					<input type="submit" id="search" class="button action" value="Search">
				</div>

			</div>

		</div><!-- .races -->
					<div class="riders col-md-6 hidden">
						<h3>Riders</h3>
						<div class="filters">
							<form name="rider_filters" id="rider_filters">
								<div class="row">
									<div class="season col-md-4">
										<h4>Season</h4>
										<select name="season" class="season-dd">
											<option value="0">-- Select One --</option>
											<?php foreach ($seasons as $season) : ?>
												<option value="<?php echo $season; ?>"><?php echo $season; ?></option>
											<?php endforeach; ?>
										</select>
									</div>
<!--
									<div class="class col-md-4">
										<h4>Class</h4>
										<select name="class" class="class">
											<option value="0">-- Select One --</option>
											<?php foreach ($classes as $class) : ?>
												<option value="<?php echo $class; ?>"><?php echo $class; ?></option>
											<?php endforeach; ?>
										</select>
									</div>
-->
<!--
									<div class="country col-md-4">
										<h4>Country</h4>
										<select name="nat" class="nat">
											<option value="0">-- Select One --</option>
											<?php foreach ($countries as $country) : ?>
												<option value="<?php echo $country; ?>"><?php echo $country; ?></option>
											<?php endforeach; ?>
										</select>
									</div>
-->
								</div><!-- .row -->

								<div class="row">
									<div class="race-search-buttons col-md-12">
										<p>
											<button type="reset" id="form-reset" value="Reset">Reset</button>
										</p>
									</div>
								</div><!-- .row -->

							</form>
							<div class="row">
								<div class="col-md-12">
									<h4>Search by Name</h4>
								</div>
								<div class="rider-search col-md-6">
									<input id="rider-search" type="text" />
								</div>
								<div class="col-md-6">
									<button type="reset" id="clear-rider-search" value="Clear">Clear</button>
								</div>
							</div><!-- .row -->
							<div class="row">
								<div class="rider-search-results col-md-12">
									<div id="rider-search-results-text">Search Riders...</div>
								</div>
							</div><!-- .row -->
						</div><!-- .filters -->
					</div><!-- .riders -->
				</div>
				<div class="row data" id="get-race-rider">
					<?php if (isset($_GET['race_code'])) : ?>
						<?php echo $this->get_race_data($_GET['race_code']); ?>
					<?php endif; ?>
					<?php if (isset($_GET['rider'])) : ?>
						<?php echo $this->get_rider_data($_GET['rider']); ?>
					<?php endif; ?>
				</div>
			</div>
			<div id="loader">
				<div class="inner">
					<img src="<?php echo plugins_url('../images/ajax-loader.gif',__FILE__); ?>" />
				</div>
			</div>
		</div><!-- .wrap -->