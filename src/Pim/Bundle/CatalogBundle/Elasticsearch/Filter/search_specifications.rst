Search specifications ElasticSearch on Akeneo
=============================================

Needs
-----
 - apply filter on all different attribute types
 - apply sorter on all sortable attribute types (excluding for example image and multi-select)

Implementation
--------------
Query vs filters
****************

Elasticsearch proposes two ways to search documents: filters and queries. According to the official documentation ( http://www.elasticsearch.org/guide/en/elasticsearch/guide/current/_queries_and_filters.html ), the two differences between them are:

 - relevancy: query computes relevancy (scoring) while filter does not
 - full text search: filter is about exact value whereas query can do full text search (analyzed)

According to the documentation:

   "As a general rule, use query clauses for full-text search or for any condition that should affect
   the relevance score, and use filter clauses for everything else."

So in our case of the grid, where relevancy is not applied, Akeneo PIM's filters will be mostly implemented with
Elasticsearch filters, except for CONTAINS type filters that needs full-text search.


Analyzers and dynamic mapping
*****************************

Depending on the field format (string, number, date, etc....), some specific analyzers may be needed. For example, in the case of ``identifier``, a n-gram analyzer must be added to be able to search on substring. Another example are the strings that needs a multifield to store the tokenized version for full-text search purpose and an untokenized version for sorting purpose.

As new attributes can be added dynamically to Akeneo, we will use the dynamic mapping feature of Elasticsearch and provides specific suffix that will specify the analyzer to use.

For example:
 - description-text: the ``-text`` suffix is applied, meaning that we must apply a specific analyzer for text


List of suffix and their mapping to Akeneo
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

===============================   ==========================
Akeneo attribute type             Elasticsearch field suffix
===============================   ==========================
 pim_catalog_identifier            -varchar
 pim_catalog_text                  -varchar
 pim_catalog_textarea              -text
 pim_catalog_metric                -metric
 pim_catalog_boolean               -boolean
 pim_catalog_simpleselect          -option
 pim_catalog_number                -integer
 pim_catalog_multiselect           -options
 pim_catalog_date                  -date
 pim_catalog_price_collection      -prices
 pim_catalog_image                 -media
 pim_catalog_file                  -media
===============================   ==========================

Common elements
***************

Naming
~~~~~~

 - Elasticsearch fields for attribute follow this naming scheme:

``attribute_code-attribute_type-locale-scope-es_suffix``

TODO: Check this ?
Please note that locale code is prefixed by ``l-`` and scope code is prefixed by ``s-``, to avoid collision between them.

Fitering
~~~~~~~~

The following example are the content of the ``query`` node in Yaml representation of the Elasticsearch JSON DSL.

They can take two different forms in our case:

TODO: Rework this part and explain the scoring difference is within the operators
If there's no Akeneo filter needing full-text capability, we will perform a ``filtered``
search without query and with only a ``bool`` filter with ``must`` typed occurence of the following form (as
Akeneo product builder supported only AND relation between conditions):

.. code-block:: yaml

    filtered:
        filter:
            bool:
                must:
                    -
                        prefix:
                             name-text: "Tshirt"
                    -
                        term:
                            price-prices: 30

If we have one or more filter needing full-text capability, we will need to combine query
and filter with a ``bool`` query with ``must`` typed occurence of the following form:

.. code-block:: yaml

    filtered:
        query:
            bool:
                must:
                    -
                        match_phrase:
                            description-text-en_US-mobile: "30 pages"
                    -
                        match_phrase:
                            name-text: "canon"
        filter:
            bool:
                must:
                    -
                        prefix:
                            name-text: "Tshirt"
                    -
                        term:
                            price-prices: 30

Sorting
~~~~~~~

 - sorting will be applied with the following ``sort`` node:

.. code-block:: yaml

    sort:
        name-text: "asc"

Sorting and tokenization
........................

Tokenized fields cannot be used for sorting as they will generate wrong results (see http://www.elasticsearch.org/guide/en/elasticsearch/guide/current/multi-fields.html).

For those fields (mainly string fields), a multi-fields must be created with the untokenized value stored in a ``raw`` subfield.

In this case, the sort becomes:

.. code-block:: yaml

    sort:
        name-text.raw: "asc"

Text area
*********
TODO: check this
:Apply: pim_catalog_textarea attributes
:Analyzer: HTML char filter + newline pattern + standard tokenizer + lowercase token filter

Other fields analyzer:
 - raw: non-tokenized (Keyword Tokenizer) + lower case token filter (HTML strip and newline pattern are applied on product normalization time, before the indexing occurs).

Data model
~~~~~~~~~~

.. code-block:: yaml

  description-text-fr_FR-mobile: "My description"


Filtering
~~~~~~~~~
Operators
.........
STARTS WITH
"""""""""""
:Specific field: raw

Must be applied on the non-analyzed version of the field or it will try to match on all tokens.

.. code-block:: php

    [
        'filter' => [
            'query_string' => [
                'default_field' => 'description-text.raw'
                'query'         => 'My*',
            ],
        ]
    ]

Note: All spaces must be escaped (with ``\\``) to prevent interpretation as separator. This applies on all query using a query_string.

Example:

.. code-block:: php

    [
        'filter' => [
            'query_string' => [
                'default_field' => 'description-text.raw'
                'query'         => 'My\\ description',
            ],
        ]
    ]


CONTAINS
""""""""
.. code-block:: php

    [
        'filter' => [
            'query_string' => [
                'default_field' => 'description-text.raw'
                'query'         => '*cool\\ product*',
            ],
        ]
    ]

DOES NOT CONTAIN
""""""""""""""""
:Specific field: raw


Same syntax than the ``contains`` but must be included in a ``must_not`` boolean occured type instead of ``filter``.
And we also need to check if the field is present within the document.

.. code-block:: php

    [
        'must_not' => [
            'query_string' => [
                'default_field' => 'description-text.raw',
                'query' => '*Do\\ not\\ want',
            ],
        ],
        'filter'   => [
            'exists' => ['field' => 'description-text'],
        ],
    ]

Equals (=)
""""""""""
:Specific field: raw

Equality will not work with tokenized field, so we will use the untokenized sub-field:

.. code-block:: php

    [
        'filter' => [
            'query_string' => [
                'default_field' => 'description-text.raw',
                'query'         => 'yeah,\ love\ description',
            ],
        ]
    ]

Not Equals (!=)
"""""""""""""""
:Specific field: raw

Same as equal, but the query is included into a `must_not' node instead of a `filter` node.

.. code-block:: php

    [
        'must_not' => [
            'query_string' => [
                'default_field' => 'description-text.raw',
                'query'         => 'yeah,\ love\ description',
            ],
        ]
    ]

EMPTY
"""""
.. code-block:: php

    [
        'filter' => [
            'exists' => ['field' => 'description-text'],
        ]
    ]

Text
****

:Apply: pim_catalog_text attributes
:Analyzer: keyword tokenizer + lowercase token filter

Other fields analyzer:
 - raw: lowercase token filter

Data model
~~~~~~~~~~
.. code-block:: yaml

  name-varchar: "My product name"

Filtering
~~~~~~~~~
Operators
.........
All operators except CONTAINS and DOES NOT CONTAINS are the same than with the text_area attributes but apply on the field directly instead of the ``.raw`` subfield.

CONTAINS
""""""""
.. code-block:: yaml

    query_string:
        default_field: "name-varchar"
        query: "*my_text*"
        analyze_wildcard: true

Note:
In case of performances problems, a faster solution would be to add a subfield with a n-gram analyzer.

DOES NOT CONTAIN
""""""""""""""""
:Type: Query

Same syntax than the contains but must be include in a ``must_not`` boolean occured type instead of ``must``.

.. code-block:: yaml

  bool:
    must_not:
        TO BE DEFINED

Identifier
**********
:Apply: pim_catalog_identifier attribute
:Analyzer: same as text

Data model
~~~~~~~~~~
.. code-block:: yaml

  sku-ident: "PRCT-1256"

Filtering
~~~~~~~~~

Operators
.........
All operators are the same as the Text field type.

Testing
-------
TODO: DIR_HERE
All queries above are (or should be) defined as integration tests scenarios in the `DIR_HERE` directory relative to this documentation.
