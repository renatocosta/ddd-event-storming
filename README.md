# DDD backend application

Full application with Domain-Driven Design approach for sustainable software.

## Table of contents

[1. Introduction](#1-Introduction)

&nbsp;&nbsp;[1.1 Purpose of this Repository](#11-purpose-of-this-repository)

[2. Domain](#2-Domain)

&nbsp;&nbsp;[2.1 Description](#21-Description)

&nbsp;&nbsp;[2.2 Event Storming](#22-event-storming)

&nbsp;&nbsp;[2.3 Aggregators](#23-Aggregators)

[3. Architecture](#3-Architecture)

&nbsp;&nbsp;[3.1 Application building blocks](#31-application-building-blocks)

&nbsp;&nbsp;[3.2 Api](#32-Api)

&nbsp;&nbsp;[3.3 Patterns](#33-patterns)

&nbsp;&nbsp;[3.4 Software Archictecture Flow Chart](#34-software-architecture-flowchart)

[4. Technology](#4-Technology)

[5. How to run](#5-how-to-run)

[6. Unit testing by Aggregator](#6-unit-testing-by-Aggregator)

[7. Logs](#7-logs)

&nbsp;&nbsp;[7.1 Domain Events](#71-domain-events)

&nbsp;&nbsp;[7.2 Dead Letter Queues (DLQs) in Kafka](#72-dead-letter-queues-(DLQs)-in-Kafka)

[8. Api security](#8-api-security)

&nbsp;&nbsp;[8.1 Protected Routes](#81-protected-routes)

[9. Data Purging](#9-data-purging)

## 1. Introduction

### 1.1 Purpose of this Repository

This is a list of the main goals of this repository:

- Presentation of the **full implementation** of an application
  - The goal is to present the implementation of an application that would be ready to run in production
- Showing the application of **best practices** and **object-oriented programming principles**
- Presentation of the use of **design patterns**. When, how and why they can be used
- Presentation of some **architectural** considerations, decisions, approaches
- Presentation of the implementation using **Domain-Driven Design** approach (**tactical** patterns)
- Presentation of the implementation of **Unit Tests** for Domain Model (Testable Design in mind)

## 2. Domain

### 2.1 Description

**Definition:**

> Domain - A sphere of knowledge, influence, or activity. The subject area to which the user applies a program is the domain of the software. [Domain-Driven Design Reference](http://domainlanguage.com/ddd/reference/), Eric Evans

### 2.2 Event Storming

Event Storming is a light, live workshop. One of the possible outputs of this workshop is presented here. Even if you are not doing Event Storming workshops, this type of process presentation can be very valuable to you and your stakeholders.

This domain was selected for the purposes of this project based on the (https://miro.com/app/board/o9J_l2Ibf8U=/?moveToWidget=3458764514498509207&cot=14) or as shown below.

![Image](./Common/Docs/event-storming.jpg?raw=true)

### 2.3 Aggregators

The main business entities/Aggregator are `Order`, `Project Reports`, `Notification` and `User`.

## 3. Architecture

### 3.1 Application building blocks

     * ApplicationLayer
          * Use Cases
          * Event Handlers
     * DomainModelLayer
        * Entity/Aggregator
        * Spec
        * Value Object
        * Event
        * Repository
        * Services
     * InfrastructureLayer
     * Ports and Adapters
        * Incoming
          * Stream
          * WebApi
        * Outgoing
          * Stream
          * WebApi

### 3.2 Api

http://{address}/api/documentation

### 3.3 Patterns

- Event Driven
- Cross-Cutting Concerns
- Api Gateway (Coming in soon)
- Unit of work
- Hexagonal (Ports and Adapters)
- CQRS
- Event sourcing (Projections)
- Outbox pattern

## 3.4 3d architecture diagram

![Image](./Common/Docs/architecture.png?raw=true)

## 4. Technology

List of technologies, frameworks and libraries used for implementation:

 - PHP 8
 - PHPUnit
 - PHP Infection - Mutation Testing Framework
 - MySQL
 - Laravel
 - Kubernetes
 - Kubernetes CronJob
 - ELK
 - Redis
 - Kafka
 - Kong
 - Codacy
 - Swagger
 - Twilio

## 5. How to run

```bash
# Kubernetes
cd Common/Deployment/manifest
kubectl apply -f php/
kubectl apply -f nginx/

cd ../
helm install mysql-chart charts/mysql/ --values charts/mysql/values.yaml
helm install redis-chart charts/redis/ --values charts/redis/values.yaml
```

## 6. Unit testing by Aggregator
```
Order ./vendor/bin/phpunit --testsuite Order

Project Reports ./vendor/bin/phpunit --testsuite ProjectReports

Notifications ./vendor/bin/phpunit --testsuite Notification

User ./vendor/bin/phpunit --testsuite User

All: ./vendor/bin/phpunit

```

![Image](./Common/Docs/unit-testing-coverage.png?raw=true)

## 7. Logs

### 7.1 Domain Events

You can watch the output of domain events occurred in the Application through Common/Framework/storage/logs/domain_events.log

```
Domain\Model\Order\Events\OrderCreated - b9ca956c-3751-4d98-9abe-18e840295815  
Domain\Model\User\Events\UserIdentified - 8c4aae10-fe7b-4983-aed5-dd107e965f0b  
Domain\Model\Notification\Events\NotificationSmsPublished  
Domain\Model\Notification\Events\NotificationSmsNotified  
Domain\Model\Order\Events\OrderConfirmed - b9ca956c-3751-4d98-9abe-18e840295815  
Domain\Model\User\Events\UserAuthenticated  
Domain\Model\Notification\Events\NotificationEmailPublished  
Domain\Model\Notification\Events\NotificationSmsPublished  
Domain\Model\User\Events\UserAssignedToOrder  
Domain\Model\Notification\Events\NotificationEmailNotified  
Domain\Model\Notification\Events\NotificationSmsNotified
Domain\Model\ProjectReports\Events\ProjectStatusChanged - b9ca956c-3751-4d98-9abe-18e840295815
```
### 7.2 Dead Letter Queues (DLQs) in Kafka

Dead Letter Queue is a secondary Kafka topic which receives the messages for which the Kafka Consumer failed to process due to certain errors like improper deserialization of message, improper message format, etc.
You can monitoring them from Slack channel #kafka-dlq-messages.

## 8. Api security

### 8.1 Protected routes

In order to verify that an incoming request is authenticated with a token the Api is taking the following abilities into consideration:

```
manage-global    
manage-order
manage-project
manage-user
```

### 9 Data Purging

In order to remove obsolete or inactive data from specific tables, An event scheduler(MySQL) will be triggered every certain period of time.
The two major benefits of having a purging strategy are runtime performance and costs saving.