'use strict';

define([
        'underscore',
        'oro/translator',
        'pim/filter/filter',
        'routing',
        'text!pim/template/filter/product/updated',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/i18n',
        'jquery.select2',
        'datepicker',
        'pim/date-context'
    ], function (
        _,
        __,
        BaseFilter,
        Routing,
        template,
        fetcherRegistry,
        userContext,
        i18n,
        initSelect2,
        Datepicker,
        DateContext
    ) {
        return BaseFilter.extend({
            shortname: 'updated',
            template: _.template(template),
            events: {
                'change [name="filter-operator"], [name="filter-value"]': 'updateState'
            },

            /**
             * Initializes configuration.
             *
             * @param config
             */
            initialize: function (config) {
                this.config = config.config;

                return BaseFilter.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
                    _.defaults(data, {field: this.getCode(), operator: _.first(_.values(this.config.operators))});
                }.bind(this));

                return BaseFilter.prototype.configure.apply(this, arguments);
            },

            /**
             * Returns rendered input.
             *
             * @return {String}
             */
            renderInput: function () {
                return this.template({
                    isEditable: this.isEditable(),
                    __: __,
                    field: this.getField(),
                    operator: this.getOperator(),
                    value: this.getValue(),
                    operatorChoices: this.config.operators
                });
            },

            /**
             * Initializes select2 and datepicker after rendering.
             */
            postRender: function () {
                this.$('[name="filter-operator"]').select2({minimumResultsForSearch: -1});

                if ('>' === this.getOperator()) {
                    Datepicker
                        .init(
                            this.$('.date-wrapper:first'),
                            {
                                format: 'yyyy-MM-dd',
                                defaultFormat: 'yyyy-MM-dd',
                                language: DateContext.get('language')
                            }
                        )
                        .on('changeDate', this.updateState.bind(this));
                }
            },

            /**
             * {@inheritdoc}
             */
            isEmpty: function () {
                return !this.getOperator() || 'ALL' === this.getOperator();
            },

            /**
             * Updates operator and value on fields change.
             * Value is reset after operator has changed.
             */
            updateState: function () {
                this.$('.date-wrapper:first').datetimepicker('hide');

                var oldOperator = this.getOperator();

                var value    = this.$('[name="filter-value"]').val();
                var operator = this.$('[name="filter-operator"]').val();

                if ('>' === operator) {
                    value = value + ' 00:00:00';
                }

                if (operator !== oldOperator) {
                    value = '';
                }

                if ('SINCE LAST JOB' === operator) {
                    value = this.getParentForm().getFormData().jobCode;
                }

                this.setData({
                    operator: operator,
                    value: value
                });

                this.render();
            }
        });
    });
