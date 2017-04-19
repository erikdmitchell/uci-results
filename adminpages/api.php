<div class="uci-results uci-results-api">

	<h2>REST API</h2>
	
	<div class="examples">
		<section>
			<p>
				A PHP Example
			</p>
			<code>
				$response = wp_remote_get('http://uci.dev/wp-json/uci/v1/races');
			</code>
		</section>
		
		<section>
			<h3>Races</h3>

			<p>
				This takes a lot of the get_posts params. Most importantly it can take in page and per page.
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/races
			</code>
			
			<p>
				To get a specific race, just pass the id.
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/races/{ID}
			</code>
		</section>

		<section>
			<h3>Riders</h3>

			<p>
				This takes a lot of the get_posts params. Most importantly it can take in page and per page.
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/riders
			</code>
			
			<p>
				To get a specific rider, just pass the id.
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/riders/{ID}
			</code>
			
			<p>
				<strong>Params</strong>
				
				<ul class="params">
					<li>results <i>boolean</i> (false)</li>
					<li>lastrace <i>boolean</i> (false)</li>
					<li>stats <i>boolean</i> (false)</li>
				</ul>
			</p>
			
		</section>

		<section>
			<h3>Countries</h3>

			<p>
				Counties applies to both races and riders.
			</p>
			
			<p>
				Structural information:
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/taxonomies/country
			</code>
			
			<p>
				List the countries:
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/country
			</code>
			
			<p>
				To get a specific country, just pass the id.
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/country/{ID}
			</code>
		</section>

		<section>
			<h3>Race Class (Class)</h3>

			<p>
				Structural information:
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/taxonomies/race_class
			</code>
			
			<p>
				List the classes:
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/race_class
			</code>
			
			<p>
				To get a specific class, just pass the id.
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/race_class/{ID}
			</code>
		</section>

		<section>
			<h3>Series (races)</h3>

			<p>
				Structural information:
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/taxonomies/series
			</code>
			
			<p>
				List the series:
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/series
			</code>
			
			<p>
				To get a specific series, just pass the id.
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/series/{ID}
			</code>
		</section>

		<section>
			<h3>Season (races)</h3>

			<p>
				Structural information:
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/taxonomies/season
			</code>
			
			<p>
				List the seasons:
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/season
			</code>
			
			<p>
				To get a specific season, just pass the id.
			</p>
			
			<code>
				http://uci.dev/wp-json/uci/v1/season/{ID}
			</code>
		</section>
		
	</div>

</div>