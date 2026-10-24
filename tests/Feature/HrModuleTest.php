<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\StaffAttendance;
use App\Models\StaffLeaveRequest;
use App\Models\StaffMember;
use App\Models\StaffPerformanceReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrModuleTest extends TestCase
{
    use RefreshDatabase;

    private function createStaffMember(): StaffMember
    {
        $organization = Organization::factory()->create();

        return StaffMember::factory()->create(['organization_id' => $organization->id]);
    }

    public function test_attendance_record_can_be_created_for_staff_member(): void
    {
        $staffMember = $this->createStaffMember();

        $attendance = StaffAttendance::query()->create([
            'staff_member_id' => $staffMember->id,
            'date' => today()->toDateString(),
            'clock_in_time' => now()->setTime(8, 0),
            'status' => 'present',
        ]);

        $this->assertDatabaseHas('staff_attendances', [
            'staff_member_id' => $staffMember->id,
            'status' => 'present',
        ]);

        $this->assertNotNull($attendance->id);
    }

    public function test_staff_member_can_clock_out(): void
    {
        $staffMember = $this->createStaffMember();

        $attendance = StaffAttendance::query()->create([
            'staff_member_id' => $staffMember->id,
            'date' => today()->toDateString(),
            'clock_in_time' => now()->setTime(8, 0),
            'status' => 'present',
        ]);

        $attendance->update(['clock_out_time' => now()->setTime(17, 0)]);

        $this->assertNotNull($attendance->fresh()->clock_out_time);
    }

    public function test_attendance_relationship_on_staff_member(): void
    {
        $staffMember = $this->createStaffMember();

        StaffAttendance::query()->create([
            'staff_member_id' => $staffMember->id,
            'date' => today()->toDateString(),
            'clock_in_time' => now()->setTime(9, 0),
            'status' => 'present',
        ]);

        $this->assertCount(1, $staffMember->fresh()->attendances);
    }

    public function test_staff_member_can_submit_leave_request(): void
    {
        $staffMember = $this->createStaffMember();

        $leave = StaffLeaveRequest::query()->create([
            'staff_member_id' => $staffMember->id,
            'start_date' => today()->addDays(3)->toDateString(),
            'end_date' => today()->addDays(7)->toDateString(),
            'leave_type' => 'annual',
            'status' => 'pending',
            'reason' => 'Family vacation',
        ]);

        $this->assertDatabaseHas('staff_leave_requests', [
            'staff_member_id' => $staffMember->id,
            'leave_type' => 'annual',
            'status' => 'pending',
        ]);

        $this->assertNotNull($leave->id);
    }

    public function test_leave_request_status_can_be_approved(): void
    {
        $staffMember = $this->createStaffMember();

        $leave = StaffLeaveRequest::query()->create([
            'staff_member_id' => $staffMember->id,
            'start_date' => today()->addDays(1)->toDateString(),
            'end_date' => today()->addDays(2)->toDateString(),
            'leave_type' => 'sick',
            'status' => 'pending',
        ]);

        $leave->update(['status' => 'approved']);

        $this->assertSame('approved', $leave->fresh()->status);
    }

    public function test_leave_request_relationship_on_staff_member(): void
    {
        $staffMember = $this->createStaffMember();

        StaffLeaveRequest::query()->create([
            'staff_member_id' => $staffMember->id,
            'start_date' => today()->addDay()->toDateString(),
            'end_date' => today()->addDays(3)->toDateString(),
            'leave_type' => 'annual',
            'status' => 'pending',
        ]);

        $this->assertCount(1, $staffMember->fresh()->leaveRequests);
    }

    public function test_performance_review_can_be_created(): void
    {
        $staffMember = $this->createStaffMember();
        $reviewer = $this->createStaffMember();

        $review = StaffPerformanceReview::query()->create([
            'staff_member_id' => $staffMember->id,
            'reviewer_id' => $reviewer->id,
            'review_date' => today()->toDateString(),
            'rating' => 4,
            'comments' => 'Great performance this quarter.',
            'productivity_score' => 88.50,
        ]);

        $this->assertDatabaseHas('staff_performance_reviews', [
            'staff_member_id' => $staffMember->id,
            'rating' => 4,
        ]);

        $this->assertNotNull($review->id);
    }

    public function test_performance_review_belongs_to_reviewer(): void
    {
        $staffMember = $this->createStaffMember();
        $reviewer = $this->createStaffMember();

        $review = StaffPerformanceReview::query()->create([
            'staff_member_id' => $staffMember->id,
            'reviewer_id' => $reviewer->id,
            'review_date' => today()->toDateString(),
            'rating' => 3,
        ]);

        $this->assertSame($reviewer->id, $review->reviewer->id);
    }

    public function test_performance_review_relationship_on_staff_member(): void
    {
        $staffMember = $this->createStaffMember();
        $reviewer = $this->createStaffMember();

        StaffPerformanceReview::query()->create([
            'staff_member_id' => $staffMember->id,
            'reviewer_id' => $reviewer->id,
            'review_date' => today()->toDateString(),
            'rating' => 5,
        ]);

        $this->assertCount(1, $staffMember->fresh()->performanceReviews);
    }
}
