Feature: comments
  Scenario: Creating a comment on a file belonging to myself
    Given user "user0" exists
    And As an "user0"
    And User "user0" uploads file "data/textfile.txt" to "/myFileToComment.txt"
    And "User0" posts a comment with content "My first comment" on the file named "/myFileToComment.txt" it should return "201"
    Then As "user0" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain a property "oc:parentId" with value "0"
    And the response should contain a property "oc:childrenCount" with value "0"
    And the response should contain a property "oc:verb" with value "comment"
    And the response should contain a property "oc:actorType" with value "users"
    And the response should contain a property "oc:objectType" with value "files"
    And the response should contain a property "oc:message" with value "My first comment"
    And the response should contain a property "oc:actorDisplayName" with value "user0"
    And the response should contain only "1" comments
    Then User "user0" deletes file "/myFileToComment.txt"

  Scenario: Creating a comment on a shared file belonging to another user
    Given user "user0" exists
    Given user "user1" exists
    And As an "user0"
    And User "user0" uploads file "data/textfile.txt" to "/myFileToComment.txt"
    And As "user0" sending "POST" to "/apps/files_sharing/api/v1/shares" with
      | path | myFileToComment.txt |
      | shareWith | user1 |
      | shareType | 0 |
    And "user1" posts a comment with content "A comment from another user" on the file named "/myFileToComment.txt" it should return "201"
    Then As "user1" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain a property "oc:parentId" with value "0"
    And the response should contain a property "oc:childrenCount" with value "0"
    And the response should contain a property "oc:verb" with value "comment"
    And the response should contain a property "oc:actorType" with value "users"
    And the response should contain a property "oc:objectType" with value "files"
    And the response should contain a property "oc:message" with value "A comment from another user"
    And the response should contain a property "oc:actorDisplayName" with value "user1"
    And the response should contain only "1" comments
    Then User "user0" deletes file "/myFileToComment.txt"

  Scenario: Creating a comment on a non-shared file belonging to another user
    Given user "user0" exists
    Given user "user1" exists
    And As an "user0"
    And User "user0" uploads file "data/textfile.txt" to "/myFileToComment.txt"
    When "user1" posts a comment with content "My first comment" on the file named "/myFileToComment.txt" it should return "404"
    Then User "user0" deletes file "/myFileToComment.txt"

  Scenario: Reading comments on a non-shared file belonging to another user
    Given user "user0" exists
    Given user "user1" exists
    And As an "user0"
    And User "user0" uploads file "data/textfile.txt" to "/myFileToComment.txt"
    Then As "user1" load all the comments of the file named "/myFileToComment.txt" it should return "404"
    Then User "user0" deletes file "/myFileToComment.txt"

  Scenario: Deleting my own comments on a file belonging to myself
    Given user "user0" exists
    And As an "user0"
    And User "user0" uploads file "data/textfile.txt" to "/myFileToComment.txt"
    And "User0" posts a comment with content "My first comment" on the file named "/myFileToComment.txt" it should return "201"
    Then As "user0" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain a property "oc:parentId" with value "0"
    And the response should contain a property "oc:childrenCount" with value "0"
    And the response should contain a property "oc:verb" with value "comment"
    And the response should contain a property "oc:actorType" with value "users"
    And the response should contain a property "oc:objectType" with value "files"
    And the response should contain a property "oc:message" with value "My first comment"
    And the response should contain a property "oc:actorDisplayName" with value "user0"
    And the response should contain only "1" comments
    Then As "user0" delete the created comment it should return "204"
    Then As "user0" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain only "0" comments
    Then User "user0" deletes file "/myFileToComment.txt"

  Scenario: Deleting my own comments on a file shared by somebody else
    Given user "user0" exists
    Given user "user1" exists
    And As an "user0"
    And User "user0" uploads file "data/textfile.txt" to "/myFileToComment.txt"
    And As "user0" sending "POST" to "/apps/files_sharing/api/v1/shares" with
      | path | myFileToComment.txt |
      | shareWith | user1 |
      | shareType | 0 |
    And "user1" posts a comment with content "My first comment" on the file named "/myFileToComment.txt" it should return "201"
    Then As "user1" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain a property "oc:parentId" with value "0"
    And the response should contain a property "oc:childrenCount" with value "0"
    And the response should contain a property "oc:verb" with value "comment"
    And the response should contain a property "oc:actorType" with value "users"
    And the response should contain a property "oc:objectType" with value "files"
    And the response should contain a property "oc:message" with value "My first comment"
    And the response should contain a property "oc:actorDisplayName" with value "user1"
    And the response should contain only "1" comments
    Then As "user1" delete the created comment it should return "204"
    Then As "user1" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain only "0" comments
    Then User "user0" deletes file "/myFileToComment.txt"

  Scenario: Deleting my own comments on a file unshared by someone else
    Given user "user0" exists
    Given user "user1" exists
    And As an "user0"
    And User "user0" uploads file "data/textfile.txt" to "/myFileToComment.txt"
    And As "user0" sending "POST" to "/apps/files_sharing/api/v1/shares" with
      | path | myFileToComment.txt |
      | shareWith | user1 |
      | shareType | 0 |
    And "user1" posts a comment with content "My first comment" on the file named "/myFileToComment.txt" it should return "201"
    Then As "user1" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain a property "oc:parentId" with value "0"
    And the response should contain a property "oc:childrenCount" with value "0"
    And the response should contain a property "oc:verb" with value "comment"
    And the response should contain a property "oc:actorType" with value "users"
    And the response should contain a property "oc:objectType" with value "files"
    And the response should contain a property "oc:message" with value "My first comment"
    And the response should contain a property "oc:actorDisplayName" with value "user1"
    And the response should contain only "1" comments
    And As "user0" remove all shares from the file named "/myFileToComment.txt"
    Then As "user1" delete the created comment it should return "404"
    Then As "user1" load all the comments of the file named "/myFileToComment.txt" it should return "404"
    Then User "user0" deletes file "/myFileToComment.txt"

  Scenario: Edit my own comments on a file belonging to myself
    Given user "user0" exists
    And As an "user0"
    And User "user0" uploads file "data/textfile.txt" to "/myFileToComment.txt"
    And "User0" posts a comment with content "My first comment" on the file named "/myFileToComment.txt" it should return "201"
    Then As "user0" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain a property "oc:parentId" with value "0"
    And the response should contain a property "oc:childrenCount" with value "0"
    And the response should contain a property "oc:verb" with value "comment"
    And the response should contain a property "oc:actorType" with value "users"
    And the response should contain a property "oc:objectType" with value "files"
    And the response should contain a property "oc:message" with value "My first comment"
    And the response should contain a property "oc:actorDisplayName" with value "user0"
    And the response should contain only "1" comments
    Then As "user0" edit the last created comment and set text to "My edited comment" it should return "207"
    Then As "user0" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain a property "oc:parentId" with value "0"
    And the response should contain a property "oc:childrenCount" with value "0"
    And the response should contain a property "oc:verb" with value "comment"
    And the response should contain a property "oc:actorType" with value "users"
    And the response should contain a property "oc:objectType" with value "files"
    And the response should contain a property "oc:message" with value "My edited comment"
    And the response should contain a property "oc:actorDisplayName" with value "user0"
    Then User "user0" deletes file "/myFileToComment.txt"

  Scenario: Edit my own comments on a file shared by someone with me
    Given user "user0" exists
    Given user "user1" exists
    And As an "user0"
    And User "user0" uploads file "data/textfile.txt" to "/myFileToComment.txt"
    And As "user0" sending "POST" to "/apps/files_sharing/api/v1/shares" with
      | path | myFileToComment.txt |
      | shareWith | user1 |
      | shareType | 0 |
    And "User1" posts a comment with content "My first comment" on the file named "/myFileToComment.txt" it should return "201"
    Then As "user0" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain a property "oc:parentId" with value "0"
    And the response should contain a property "oc:childrenCount" with value "0"
    And the response should contain a property "oc:verb" with value "comment"
    And the response should contain a property "oc:actorType" with value "users"
    And the response should contain a property "oc:objectType" with value "files"
    And the response should contain a property "oc:message" with value "My first comment"
    And the response should contain a property "oc:actorDisplayName" with value "user1"
    And the response should contain only "1" comments
    Then As "user1" edit the last created comment and set text to "My edited comment" it should return "207"
    Then As "user1" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain a property "oc:parentId" with value "0"
    And the response should contain a property "oc:childrenCount" with value "0"
    And the response should contain a property "oc:verb" with value "comment"
    And the response should contain a property "oc:actorType" with value "users"
    And the response should contain a property "oc:objectType" with value "files"
    And the response should contain a property "oc:message" with value "My edited comment"
    And the response should contain a property "oc:actorDisplayName" with value "user1"
    Then User "user0" deletes file "/myFileToComment.txt"

  Scenario: Edit my own comments on a file unshared by someone with me
    Given user "user0" exists
    Given user "user1" exists
    And As an "user0"
    And User "user0" uploads file "data/textfile.txt" to "/myFileToComment.txt"
    And As "user0" sending "POST" to "/apps/files_sharing/api/v1/shares" with
      | path | myFileToComment.txt |
      | shareWith | user1 |
      | shareType | 0 |
    And "User1" posts a comment with content "My first comment" on the file named "/myFileToComment.txt" it should return "201"
    Then As "user0" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain a property "oc:parentId" with value "0"
    And the response should contain a property "oc:childrenCount" with value "0"
    And the response should contain a property "oc:verb" with value "comment"
    And the response should contain a property "oc:actorType" with value "users"
    And the response should contain a property "oc:objectType" with value "files"
    And the response should contain a property "oc:message" with value "My first comment"
    And the response should contain a property "oc:actorDisplayName" with value "user1"
    And the response should contain only "1" comments
    And As "user0" remove all shares from the file named "/myFileToComment.txt"
    Then As "user1" edit the last created comment and set text to "My edited comment" it should return "404"
    Then As "user0" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain a property "oc:parentId" with value "0"
    And the response should contain a property "oc:childrenCount" with value "0"
    And the response should contain a property "oc:verb" with value "comment"
    And the response should contain a property "oc:actorType" with value "users"
    And the response should contain a property "oc:objectType" with value "files"
    And the response should contain a property "oc:message" with value "My first comment"
    And the response should contain a property "oc:actorDisplayName" with value "user1"
    Then User "user0" deletes file "/myFileToComment.txt"

  Scenario: Edit comments of other users should not be possible
    Given user "user0" exists
    Given user "user1" exists
    And As an "user0"
    And User "user0" uploads file "data/textfile.txt" to "/myFileToComment.txt"
    And As "user0" sending "POST" to "/apps/files_sharing/api/v1/shares" with
      | path | myFileToComment.txt |
      | shareWith | user1 |
      | shareType | 0 |
    And "user1" posts a comment with content "My first comment" on the file named "/myFileToComment.txt" it should return "201"
    Then As "user0" load all the comments of the file named "/myFileToComment.txt" it should return "207"
    And the response should contain a property "oc:parentId" with value "0"
    And the response should contain a property "oc:childrenCount" with value "0"
    And the response should contain a property "oc:verb" with value "comment"
    And the response should contain a property "oc:actorType" with value "users"
    And the response should contain a property "oc:objectType" with value "files"
    And the response should contain a property "oc:message" with value "My first comment"
    And the response should contain a property "oc:actorDisplayName" with value "user1"
    And the response should contain only "1" comments
    Then As "user0" edit the last created comment and set text to "My edited comment" it should return "403"
    Then User "user0" deletes file "/myFileToComment.txt"