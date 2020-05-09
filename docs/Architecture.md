Stack: Linux, Nginx, MongoDB, PHP

Tools: Composer, PHPDI

Package structure: https://github.com/php-pds/skeleton

HTTP Framework: https://www.slimframework.com/

A 'Clean Architecture' has been implemented through a DDD pattern. 
A quick test of this design is to start in the 'Domain' directory and test that there are no 'use' imports from the Application or Infrastructure layers.

For a definition of a clean architecture we can look to Uncle Bob's description:
- **Independent of Frameworks.** The architecture does not depend on the existence of some library of feature laden software. This allows you to use such frameworks as tools, rather than having to cram your system into their limited constraints.
- **Testable.** The business rules can be tested without the UI, Database, Web Server, or any other external element. Independent of UI. The UI can change easily, without changing the rest of the system. A Web UI could be replaced with a console UI, for example, without changing the business rules.
- **Independent of Database.** You can swap out Oracle or SQL Server, for Mongo, BigTable, CouchDB, or something else. Your business rules are not bound to the database.
- **Independent of any external agency.** In fact your business rules simply don’t know anything at all about the outside world.

A rough break down of the layers:
- **Domain:** Contains entities and core logic as well as defining interfaces to communicate with the Application and Infrastructure layers.
- **Application:** Provides the application implementation code by defining the HTTP server. It provides an interaction layer in the form of an API, it accepts requests and does the work of authenticating and translating them into the language of the Domain layer.
- **Infrastructure:** Provides data to the domain, often from a database but also from other services.
