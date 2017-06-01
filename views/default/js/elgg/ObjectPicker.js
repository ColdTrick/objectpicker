/** @module elgg/ObjectPicker */

define(['jquery', 'elgg'], function ($, elgg) {

	/**
	 * @param {HTMLElement} wrapper outer div
	 * @constructor
	 * @alias module:elgg/ObjectPicker
	 *
	 * @todo move this to /js/classes ?
	 */
	function ObjectPicker(wrapper) {
		this.$wrapper = $(wrapper);
		this.$input = $('.elgg-input-object-picker', wrapper);
		this.$ul = $('.elgg-object-picker-list', wrapper);

		var self = this,
			data = this.$wrapper.data();

		this.name = data.name || 'objects';
		this.handler = data.handler || 'livesearch';
		this.limit = data.limit || 0;
		this.minLength = data.minLength || 2;
		this.subtype = data.subtype || null;
		this.isSealed = false;
				
		this.$input.autocomplete({
			source: function(request, response) {
				// note: "this" below will not be bound to the input, but rather
				// to an object created by the autocomplete component

				if (self.isSealed) {
					return;
				}
				
				var tempData = self.$wrapper.data();

				elgg.get(self.handler, {
					data: {
						term: this.term,
						"match_on[]": 'objects',
						name: self.name,
						subtype: self.subtype,
						container_guid: tempData.container_guid
					},
					dataType: 'json',
					success: function(data) {
						response(data);
					}
				});
			},
			minLength: self.minLength,
			html: "html",
			select: function(event, ui) {
				
				if (typeof ui.item !== 'object' || ui.item === null) {
					// need to have something selected, (this shouldn't happen)
					return;
				}
				
				if (ui.item.type !== 'object') {
					// since this is the objectpicker supported results are objects
					return;
				}
				
				self.addObject(event, ui.item.guid, ui.item.html);
			},
			// turn off experimental live help - no i18n support and a little buggy
			messages: {
				noResults: '',
				results: function() {}
			},
			close: function (event) {
				self.clearList();
			}
		}).blur(function(event) {
			self.clearList();
		});
		
		this.$wrapper.on('click', '.elgg-object-picker-remove', function(event) {
			self.removeObject(event);
		});

		
		this.enforceLimit();
	}

	ObjectPicker.prototype = {
		/**
		 * Adds a user to the select user list
		 *
		 * @param {Object} event
		 * @param {Number} guid    GUID of autocomplete item selected by user
		 * @param {String} html    HTML for autocomplete item selected by user
		 */
		addObject : function(event, guid, html) {
			// do not allow objects to be added multiple times
			if (!$('li[data-guid="' + guid + '"]', this.$ul).length) {
				this.$ul.append(html);
			}
			this.$input.val('');

			this.enforceLimit();

			event.preventDefault();
		},

		/**
		 * Removes a user from the select user list
		 *
		 * @param {Object} event
		 */
		removeObject : function(event) {
			$(event.target).closest('.elgg-object-picker-list > li').remove();

			this.enforceLimit();

			event.preventDefault();
		},

		/**
		 * Make sure user can't add more than limit
		 */
		enforceLimit : function() {
			if (this.limit) {
				if ($('li[data-guid]', this.$ul).length >= this.limit) {
					if (!this.isSealed) {
						this.seal();
					}
				} else {
					if (this.isSealed) {
						this.unseal();
					}
				}
			}
		},

		/**
		 * Don't allow user to add users
		 */
		seal : function() {
			this.$input.prop('disabled', true);
			this.$wrapper.addClass('elgg-state-disabled');
			this.isSealed = true;
		},

		/**
		 * Allow user to add users
		 */
		unseal : function() {
			this.$input.prop('disabled', false);
			this.$wrapper.removeClass('elgg-state-disabled');
			this.isSealed = false;
		},
		
		/**
		 * Cleans up dropdown list if no longer needed, to prevent stray input fields
		 */
		clearList : function() {
			this.$wrapper.find(".ui-autocomplete").html("");
		}
	};

	/**
	 * @param {String} selector
	 */
	ObjectPicker.setup = function(selector) {
		elgg.register_hook_handler('init', 'system', function () {
			$(selector).each(function () {
				// we only want to wrap each picker once
				if (!$(this).data('initialized')) {
					new ObjectPicker(this);
					$(this).data('initialized', 1);
				}
			});
		});
	};

	return ObjectPicker;
});