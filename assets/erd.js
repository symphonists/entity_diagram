EntityDiagram = {
		
	container: null,
	sections: [],
	sections_fit: [],
	row_height: 0,	
	
	init: function() {
		
		// keep a reference to current object for use within anonymous
		// functions/closures to maintain scope
		var self = this;
		
		this.container = $("div#diagram");
		
		// jQuery reference to all sections
		var $s = $(".section", this.container);
		// internal reference to sections and their meta data
		var s = [];
		
		// cache a reference to the ID, height and jQuery (DOM) reference for each section
		// makes it easier for sorting and iterating later
		$s.each(function(i) {
			var height = $(this).height() + 2;
			var id = $(this).attr("id").replace(/section-/,"");
			s.push({ id: id, height: height, element: $(this) });
		});
		
		// sort sections in descending height
		this.sections = s.sort(this.sortDescending);
		
		
		// width of a section block
		var section_width = $s.width() + parseInt($s.css("margin-left"));
		
		// width of the container into which sections are added
		var page_width = this.container.width() - (parseInt(this.container.css("padding-left")) + parseInt(this.container.css("padding-right")));

		// how many sections can fit horizontally across the page
		var columns_per_row = Math.floor(page_width / section_width);

		// we have cached the DOM nodes in the sections[] array
		// so remove the original DOM nodes from the page
		$s.remove();

		// add the first row
		this.container.append("<div class='row'></div>");
		var current_row_index = 0;
		
		// grab this new row to add new sections to
		var current_row = $("div.row:eq(" + current_row_index + ")", this.container);
		
		var columns_this_row = 0;
		
		// loop through all of our original sections
		for (var i=0; i < this.sections.length; i++) {
			
			var section = this.sections[i];
			
			// test whether this section has already been added to the DOM
			// we store an array of added section IDs to prevent potentially
			// slow DOM lookups
			var is_already_added = false;
			for (var j=0; j < this.sections_fit.length; j++) {
				if (this.sections_fit[j] == section.id) {
					is_already_added = true;
				}
			}
			
			// if this section hasn't yet been added back to the DOM
			if (!is_already_added) {
				
				// if we've added as many sections horizontally that will fit
				// add a new row and reset the 'this row' counter
				if (columns_this_row == columns_per_row) {
					this.container.append("<div class='row'></div>");
					current_row_index++;
					current_row = $("div.row:eq(" + current_row_index + ")", this.container);
					columns_this_row = 0;
				}

				// add a column to the current row
				var column = $("<div class='column'></div>");
				column.append(section.element);

				this.sections_fit.push(section.id);

				// if this is the first section in this row, set the row height to the
				// height of this section. This forces further columns to use this height
				// when stacking sections vertically
				if (columns_this_row == 0) {
					this.row_height = section.height;
				}
				
				// fit as many other sections into this column
				this.fitSmallSections(column, section.height);

				// now we can add this column to the DOM, to the current row
				current_row.append(column);
				
				// we've added another section column
				columns_this_row++;

			}

		}
		
		// select all new sections. Can't use $s is this contains a ref to the old (now removed) sections
		$("div.section").css("visibility", "visible");
		
		// add hover state to show edit hyperlinks on section headings
		$(".section h3", this.container).hover(function() {
			$("a.edit", this).show();
		}, function() {
			$("a.edit", this).hide();
		});

		// add hover state to highlight related section links
		$(".section .field", this.container).hover(function() {
			var section_links = $(".section-link", this);
			// if this field is section linked
			if (section_links.length > 0) {
				$(this).addClass("highlighted");
				section_links.each(function(i) {
					// parse the section link ID from the class attribute
					var id = $(this).attr("class").replace(/section-link section-link-/,"");
					// highlight the section link in the related section
					$(".section-link-" + id, self.container).parents(".field").addClass("highlighted");
				});
			}
		}, function() {
			$(".highlighted").removeClass("highlighted");
		});
		
		$("#field-57 .section-link").each(function() {
			var id = $(this).attr("class").replace(/section-link section-link-/,"");
			$(".section-link-" + id, self.container).parents(".field").addClass("highlighted");
		});
		
		$("h2").append("<label><input type='checkbox' id='advanced-info' />Show section and field IDs</label>");

		$("#advanced-info").change(function() {
			if ($(this).is(":checked")) {
				self.container.addClass("advanced");
			} else {
				self.container.removeClass("advanced");
			}
		});
		
	},
		
	fitSmallSections: function(column, max_height) {
		// height remaining to fill in this column
		var height_remaining = this.row_height - max_height;
		// loop through alll sections
		for (var i=0; i < this.sections.length; i++) {
			var section = this.sections[i];
			
			var is_already_added = false;
			for (var j=0; j < this.sections_fit.length; j++) {
				if (this.sections_fit[j] == section.id) {
					is_already_added = true;
				}
			}
			
			// only use sections that don't yet exist in the DOM			
			if (!is_already_added) {
				// if this section would fit in the remaining height
				if (section.height <= height_remaining) {
					// add margin, add the section to the DOM and amend the height remaining
					section.element.css("margin-top", "20px");
					column.append(section.element);
					height_remaining = height_remaining - section.height;
					
					this.sections_fit.push(section.id);
					
				}
			}
		}
	},
	
	sortDescending: function(a, b) {
		return b.height - a.height;
	}
};

$(document).ready(function() {
	EntityDiagram.init();
});