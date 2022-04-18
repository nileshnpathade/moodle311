@mod @mod_virtual
Feature: Configure virtual appearance
  In order to change the appearance of the virtual resource
  As an admin
  I need to configure the virtual appearance settings

  Background:
    Given the following "courses" exist:
      | shortname | fullname   |
      | C1        | Course 1 |
    And the following "activities" exist:
      | activity | name       | intro      | course | idnumber |
      | virtual     | virtualName1  | virtualDesc1  | C1     | virtual1    |

  @javascript
  Scenario Outline: Hide and display virtual features
    Given I am on the "virtualName1" "virtual activity editing" virtual logged in as admin
    And I expand all fieldsets
    And I set the field "Display virtual name" to "<value>"
    And I press "Save and display"
    Then I <shouldornot> see "virtualName1" in the "region-main" "region"

    Examples:
      | feature                    | lookfor        | value | shouldornot |
      | Display virtual name          | virtualName1      | 1     | should      |
      | Display virtual name          | virtualName1      | 0     | should not  |
      | Display virtual description   | virtualDesc1      | 1     | should      |
      | Display virtual description   | virtualDesc1      | 0     | should not  |
      | Display last modified date | Last modified: | 1     | should      |
      | Display last modified date | Last modified: | 0     | should not  |
