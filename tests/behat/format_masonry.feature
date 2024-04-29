@ewallah @format @format_masonry @javascript

Feature: format_masonry
  In order to view my course contents I have to browse

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format  | coursedisplay | numsections |
      | Course 1 | C1        | masonry | 1             | 5           |
    And the following "users" exist:
      | username |
      | teacher1 |
      | student1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity | name       | intro                     | course | idnumber  | section | visible |
      | lesson   | lesson 1   | Test lesson description   | C1     | lesson1   | 1       | 1       |
      | lesson   | lesson 2   | Test lesson description   | C1     | lesson2   | 1       | 1       |
      | lesson   | lesson 3   | Test lesson description   | C1     | lesson3   | 1       | 0       |
      | book     | book 1     | Test book description     | C1     | book1     | 2       | 1       |
      | book     | book 2     | Test book description     | C1     | book2     | 2       | 1       |
      | book     | book 3     | Test book description     | C1     | book3     | 2       | 0       |
      | data     | data 1     | Test data description     | C1     | data1     | 3       | 1       |
      | data     | data 2     | Test data description     | C1     | data2     | 3       | 1       |
      | data     | data 3     | Test data description     | C1     | data3     | 3       | 0       |
      | choice   | choice 1   | Test choice description   | C1     | choice1   | 4       | 1       |
      | choice   | choice 2   | Test choice description   | C1     | choice2   | 4       | 1       |
      | choice   | choice 3   | Test choice description   | C1     | choice3   | 4       | 0       |
      | page     | page 1     | Test page description     | C1     | page1     | 5       | 1       |
      | page     | page 2     | Test page description     | C1     | page2     | 5       | 1       |
      | page     | page 3     | Test page description     | C1     | page3     | 5       | 0       |
      | workshop | workshop 1 | Test workshop description | C1     | workshop1 | 6       | 1       |
      | workshop | workshop 2 | Test workshop description | C1     | workshop2 | 6       | 1       |
      | workshop | workshop 3 | Test workshop description | C1     | workshop3 | 6       | 0       |

  Scenario: Empty section 0 stays visible for teachers in masonry topics
    Given I am on the "C1" "Course" page logged in as "teacher1"
    Then I should see "General" in the ".course-content" "css_element"

  Scenario: Students doe not see stealth topics in masonry topics
    Given I am on the "C1" "Course" page logged in as "student1"
    Then I should not see "General" in the ".course-content" "css_element"
    And I should not see "workshop 1" in the ".course-content" "css_element"

  Scenario: Non empty section 0 is shown in masonry topics
    Given the following "activities" exist:
      | activity | name      | intro                    | course | idnumber | section | visible |
      | page     | page 4    | Test lesson description  | C1     | page4    | 0       | 1       |
    And  I am on the "C1" "Course" page logged in as "teacher1"
    Then I should see "General" in the "li#section-0" "css_element"
    And I log out
    And  I am on the "C1" "Course" page logged in as "student1"
    Then I should see "General" in the "li#section-0" "css_element"

  Scenario: The modules should be visible and hidden in masonry format
    Given I am on the "C1" "Course" page logged in as "teacher1"
    Then I should see "lesson 1"
    And I should see "lesson 2"
    And I should see "lesson 3"
    And I should see "page 1"
    And I should see "page 2"
    And I should see "page 3"
    And I log out
    When I am on the "C1" "Course" page logged in as "student1"
    Then I should see "lesson 1"
    And I should see "lesson 2"
    And I should not see "lesson 3"
    And I should see "book 1"
    And I should see "book 2"
    And I should not see "book 3"
    And I should see "page 1"
    And I should see "page 2"
    And I should not see "page 3"
    # Stealth section is hidden.
    And I should not see "workshop 1"
    And I should not see "workshop 2"
    And I should not see "workshop 3"

  Scenario: Modify section summary - title - background color in masonry format
    Given I am on the "C1" "Course" page logged in as "teacher1"
    And I turn editing mode on
    And I close block drawer if open
    And I edit the section "1"
    And I set the following fields to these values:
      | Description | Welcome |
    And I press "Save changes"
    Then I should see "Welcome" in the "li#section-1" "css_element"

    When I edit the section "1"
    And I set the following fields to these values:
      | Section name             | first  |
      | Section Background color | yellow |
    And I press "Save changes"
    And I close block drawer if open
    And I edit the section "2"
    And I set the following fields to these values:
      | Section Background color | #f0f0f0 |
    And I press "Save changes"
    And I close block drawer if open
    And I edit the section "3"
    And I set the following fields to these values:
      | Section Background color | hsla(207,38%,47%,0.8)  |
    And I press "Save changes"
    And I close block drawer if open
    And I edit the section "4"
    And I set the following fields to these values:
      | Section Background color | transparent  |
    And I press "Save changes"
    And I turn editing mode off
    Then I should see "first" in the "li#section-1" "css_element"

  Scenario: Deleting the last section in masonry format
    Given I am on the "C1" "Course" page logged in as "teacher1"
    And I turn editing mode on
    And I delete section "4"
    Then I should see "Are you absolutely sure you want to completely delete \"Topic 4\" and all the activities it contains?"
    And I press "Delete"
    And I should see "Topic 4"
    But I should not see "Topic 5"

  Scenario: Deleting the middle section in masonry format
    Given I am on the "C1" "Course" page logged in as "teacher1"
    And I turn editing mode on
    When I delete section "4"
    And I press "Delete"
    Then I should not see "Page 1"
    And I should see "Orphaned activities"
