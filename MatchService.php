<?php

namespace App\Services;

use App\Models\JobPost;
use App\Models\StudentProfile;

/*
 * THE JOB MATCHING ALGORITHM
 *
 * Total score = 100 points
 *   Skills               40 points
 *   Availability         30 points
 *   Relevant Background  30 points
 *
 * FAIRNESS: Background is full (30) if the student has a related COURSE
 * OR related EXPERIENCE. Having both does not give extra points, so a student with
 * experience and a student with a related course are treated equally.
 * The score is only a guide. It never rejects anyone and never changes a status.
 */
class MatchService
{
    // Turn a ratio (0 to 1) into a word that students and businesses can read
    public function label($ratio)
    {
        if ($ratio >= 0.8) return 'Strong';
        if ($ratio >= 0.4) return 'Partial';
        return 'Low';
    }

    public function tier($total)
    {
        if ($total >= 70) return 'Good match';
        if ($total >= 40) return 'Possible match';
        return 'Worth exploring';
    }

    // 1 = the whole shift fits in the free time, 0.5 = only part of it fits, 0 = does not fit
    private function slotScore($slot, $job)
    {
        if (!$slot) return 0;
        if ($slot->start_time <= $job->start_time && $slot->end_time >= $job->end_time) return 1;
        if ($slot->start_time < $job->end_time && $slot->end_time > $job->start_time) return 0.5;
        return 0;
    }

    // The days a job takes place. Each day has a date (if known) and a weekday number (1 = Monday ... 7 = Sunday)
    public function jobDays(JobPost $job)
    {
        $days = [];
        if ($job->job_type == '1-Day' && $job->work_date) {
            $days[] = ['date' => $job->work_date, 'weekday' => (int) date('N', strtotime($job->work_date))];
        } elseif ($job->job_type == 'Several-Day' && $job->work_date && $job->end_date) {
            $current = strtotime($job->work_date);
            $last = strtotime($job->end_date);
            $count = 0;
            while ($current <= $last && $count < 14) {            // at most 14 days
                $days[] = ['date' => date('Y-m-d', $current), 'weekday' => (int) date('N', $current)];
                $current = strtotime('+1 day', $current);
                $count++;
            }
        } else {                                                    // Recurring and Part-Time jobs use weekdays only
            foreach ($job->dayNumbers() as $number) {
                $days[] = ['date' => '', 'weekday' => (int) $number];
            }
        }
        return $days;
    }

    // Calculate the match between one student and one job
    public function getMatch(StudentProfile $student, JobPost $job)
    {
        // ---------- 1. SKILLS (40 points) ----------
        $required = $job->skills;                                   // skills the job needs
        $myIds = $student->skills->pluck('id')->toArray();          // skills the student has
        $haveCount = 0;
        foreach ($required as $skill) {
            if (in_array($skill->id, $myIds)) $haveCount++;
        }
        if (count($required) == 0) {
            $skillsRatio = 1;                                       // no skills required = everyone gets full points
            $skillsText = 'No specific skills required';
        } else {
            $skillsRatio = $haveCount / count($required);
            $skillsText = $haveCount . ' of ' . count($required) . ' required skills';
        }

        // ---------- 2. AVAILABILITY (30 points) ----------
        // For every day of the job, check the weekly schedule and the "available for this day" dates
        $weekly = $student->availabilities->keyBy('day_of_week');
        $specific = $student->dayAvailabilities->keyBy('avail_date');
        $jobDays = $this->jobDays($job);
        if (count($jobDays) == 0) {
            $availRatio = 1;
            $availText = 'No fixed schedule';
        } else {
            $sum = 0;
            $fullDays = 0;
            foreach ($jobDays as $day) {
                $fromWeekly = $this->slotScore($weekly->get($day['weekday']), $job);
                $fromDate = 0;
                if ($day['date'] != '') $fromDate = $this->slotScore($specific->get($day['date']), $job);
                $best = max($fromWeekly, $fromDate);                // use whichever fits better
                $sum = $sum + $best;
                if ($best == 1) $fullDays++;
            }
            $availRatio = $sum / count($jobDays);
            $availText = $fullDays . ' of ' . count($jobDays) . ' job day(s) fully covered by your availability';
        }

        // ---------- 3. RELEVANT BACKGROUND (30 points) ----------
        // full points if the course is preferred OR the student has experience in the job's category
        $preferred = $job->courses->pluck('id')->toArray();
        $courseMatch = (count($preferred) == 0) || in_array($student->course_id, $preferred);
        $experienceMatch = $student->experiences->where('category_id', $job->category_id)->count() > 0;
        $bgRatio = 0;
        if ($courseMatch || $experienceMatch) $bgRatio = 1;
        $bgText = 'Course: ' . ($courseMatch ? 'match' : 'different from preferred')
                . ' | Experience: ' . ($experienceMatch ? 'related experience listed' : 'none listed in this category');

        // ---------- Put it all together ----------
        $skillsPoints = round($skillsRatio * 40, 1);
        $availPoints = round($availRatio * 30, 1);
        $bgPoints = round($bgRatio * 30, 1);
        $total = (int) round($skillsPoints + $availPoints + $bgPoints);

        return [
            'total' => $total,
            'tier' => $this->tier($total),
            'skills_points' => $skillsPoints, 'skills_level' => $this->label($skillsRatio), 'skills_text' => $skillsText,
            'avail_points' => $availPoints, 'avail_level' => $this->label($availRatio), 'avail_text' => $availText,
            'bg_points' => $bgPoints, 'bg_level' => $this->label($bgRatio), 'bg_text' => $bgText,
        ];
    }
}
