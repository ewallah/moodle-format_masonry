@format @format_masonry

Feature: format_masonry
  In order to view my course contents I have to browse

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format  | coursedisplay | numsections |
      | Course 1 | C1        | masonry | 0             | 4           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity   | name                   | intro                         | course | idnumber    | section | visible |
      | assign     | assignment 1           | Test assignment description   | C1     | assign1     | 1       | 1       |
      | assign     | assignment 2           | Test assignment description   | C1     | assign2     | 1       | 1       |
      | assign     | assignment 3           | Test assignment description   | C1     | assign3     | 1       | 0       |
      | book       | book 1                 | Test book description         | C1     | book1       | 2       | 1       |
      | book       | book 2                 | Test book description         | C1     | book2       | 2       | 1       |
      | book       | book 3                 | Test book description         | C1     | book3       | 2       | 0       |
      | chat       | chat 1                 | Test chat description         | C1     | chat1       | 3       | 1       |
      | chat       | chat 2                 | Test chat description         | C1     | chat2       | 3       | 1       |
      | chat       | chat 3                 | Test chat description         | C1     | chat3       | 3       | 0       |
      | choice     | choice 1               | Test choice description       | C1     | choice1     | 4       | 1       |
      | choice     | choice 2               | Test choice description       | C1     | choice2     | 4       | 1       |
      | choice     | choice 3               | Test choice description       | C1     | choice3     | 4       | 0       |

  Scenario: Empty section 0 stays hidden
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    Then I should not see "General"

    When I turn editing mode on
    And I add a "Page" to section "0"
    And I set the following fields to these values:
      | Name         | P1 |
      | Description  | x  |
      | Page content | x  |
    And I click on "Save and return to course" "button"
    And I turn editing mode off
    Then I should see "General" in the "li#section-0" "css_element"

  @javascript
  Scenario: The modules should be visible and hidden in masonry format
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    Then I should see "assignment 1"
    And I should see "assignment 2"
    And I should see "assignment 3"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "book 1"
    And I should see "book 2"
    And I should not see "book 3"

  @javascript
  Scenario: Modify section summary - title - background color in masonry format
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I edit the section "1"
    And I set the following fields to these values:
      | Summary | Welcome |
    And I press "Save changes"
    Then I should see "Welcome" in the "li#section-1" "css_element"

    # Change some section background colors.
    # When I click on "Edit" "link" in the "li#section-1" "css_element"
    When I edit the section "1"
    And I set the following fields to these values:
      | Custom                   | true  |
      | Section name             | first |
      | Section Background color | #000  |
    And I press "Save changes"
    And I edit the section "2"
    And I set the following fields to these values:
      | Section Background color | #FFFFFF  |
    And I press "Save changes"
    And I edit the section "3"
    And I set the following fields to these values:
      | Section Background color | hsla(207,38%,47%,0.8)  |
    And I press "Save changes"
    And I edit the section "4"
    And I set the following fields to these values:
      | Section Background color | transparent  |
    And I press "Save changes"
    And I turn editing mode off
    Then I should see "first" in the "li#section-1" "css_element"

  @javascript
  Scenario: The masonry should reorder after a docking event
    Given I log in as "admin"
    And I am on site homepage
    And I navigate to "Appearance > Themes > Theme settings" in site administration
    And I click on "Allow course themes" "checkbox"
    And I press "Save changes"
    And I am on "Course 1" course homepage
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the field "Force theme" to "Clean"
    And I press "Save and display"
    And I am on site homepage
    And I follow "Course 1"
    And I dock "Navigation" block
    And I dock "Administration" block
    Then I should see "assignment 1" in the "li#section-1" "css_element"
