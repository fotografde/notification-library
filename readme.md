# Notification Library

A simple library to push data to be handled by the Notification Service

## Setup

- add to your (project folder)/composer.json
    ```
    "require": {
        ...
        "fotografde/notification-library": "*"
    }
    ```

- run 
    ```
    (project folder)> composer update
    ```

- modify the configuration file, if necessary  
    ```
    (project folder)> cp vendor/GetPhoto/notification-library/config/notification.php.default vendor/GetPhoto/notification-library/config/notification.php
    OR
    (project folder)> cp src/vendors/GetPhoto/notification-library/config/notification.php.default src/vendors/GetPhoto/notification-library/config/notification.php
    ```
        
    **OR (for local configuration) **
    ```
    (project folder)> cp config/notification.php.default config/notification.php
    ```
    
- and configure it  
    ```
    'events_table' => 'EventQueue',
    'region' => '...',
    'endpoint' => '...',
    'credential_key' => '...',
    'credential_secret' => '...',
    'notification_service_url' => '...',
    'method_add_trigger' => 'api/trigger',
    ```

## Events

### Usage

- Import the Notification class
    ```php
    use GetPhoto\Notification;
    ```
 
- Pass the data to the method to push events
    ```php
    (new Notification)->pushEvent($data);
    ```

- Examples

    - User Login
        ```php
        $data = [
            'eventTypeId' => 'User.Login',
            'dateTime' => '20170117 232900',
            'userId' => '1',
            'photographerId' => '1',
        ];
        (new Notification)->pushEvent($data);
        ```

    - New Contactsheet
        ```php
        $data = [
            'eventTypeId' => 'Contactsheet.Created',
            'dateTime' => '20170115 080005',
            'userId' => '5',
            'photographerId' => '2',
            'contactsheetId' => 'a0shfd0a9shf9',
        ];
        (new Notification)->pushEvent($data);
        ```
        
## Triggers

### Usage

- Import the Trigger class
    ```php
    use GetPhoto\Trigger;
    ```
 
- Instantiate a new object, add the options and save

    ```php
    //Trigger ID => Unique name for your trigger
    //Trigger type => EventBased, Scheduled, TimeBased or Manual
    //Trigger action => Action to be executed
    $trigger = new Trigger('trigger id', 'trigger description', 'trigger type', 'trigger action');
  
    //optional methods
    $trigger->addSubscriptionKey('value');
    $trigger->addDelay('value');
    $trigger->addWhen('value');
  
    //auxiliar methods
    $trigger->createActionParameterConstant('constant', 'value');
    $trigger->createActionParameterVariable('variable', 'value');
    $trigger->createActionParameterMethod('variable', 'method', ['arguments', ...]);

    //optional methods that can be used multiple times
    $trigger->addActionParameter($parameter);
    $trigger->addParameter(['data key' => 'data value', ...]);
    
    $trigger->save();
    ```

### Samples

#### Manual Trigger

Triggers that are executed via api request

- Return a json with all login from an user
    
    Problem: We need a trigger to feed the user's **config page** with all of his logins.
    The **config page** will make an api request whenever the user request the login data.
    
    Solution: We have the method returnEvents which filter events based on given arguments.
    In this case, we want to filter by EventTypeId and UserId.
    We can obtain the UserId from the subscription, so it is a variable.
    Since we already know the EventTypeId, we define a constant.
    
    ```php
    $trigger = new Trigger('UserLogins.v1', 'Shows all logins from an user', 'Manual', 'method::returnJson');
    $trigger->addSubscriptionKey('UserId');
  
    $userId = $trigger->createVariable('userId', 'userId');
    $eventTypeId = $trigger->createConstant('eventTypeId', 'User.Login');
    $eventsMethod = $trigger->createMethod('returnJson', [$userId, $eventTypeId]);
    $events = $trigger->createVariable('events', $eventsMethod);
   
    $trigger->addActionParameter($events);
    $trigger->save();
    ```
    
    Result: 
    
    - Trigger ID: UserLogins.v1
    - Trigger Description: Shows all logins from an user
    - Type: Manual
    - Subscription Key: UserId
    - Action: method::returnJson
    - Action Parameters: events::{{returnEvents||userId::[[userId]]||eventTypeId::User.Login}}
    
    Usage: 
    
    http://notification-service-url/queryEvents/UserLogins.v1/subscriber-id
