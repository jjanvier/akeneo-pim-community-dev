# ElasticSearchBundle

Stupid and simple [PHP ElasticSearch](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index.html) wrapper for the Symfony world.

The only purpose of this bundle is to be able to launch native ElasticSearch queries through the official PHP client provided as a Symfony service. For that, it provides the following:
    - a PHP ElasticSearch client service
    - a way to load the index configuration from several YAML files
  
No support of any sort of query builder is provided. Neither of automatically mapping entities to ElasticSearch documents. For such features, please take
a look at the excellent [ONGR Elasticsearch Bundle](https://github.com/ongr-io/ElasticsearchBundle) and [ElasticsearchDSL](https://github.com/ongr-io/ElasticsearchDSL) packages.
We didn't use those packages for two main reasons:
    - they require Symfony 2.8, which, at the time this README is written, is not compatible with our stack
    - we wanted to run pure simple and native ElasticSearch queries
    
This bundle is intended to remain simple and stupid. If at some point, we need more advanced features, we should consider replacing this bundle by something more powerful
that already exists.
