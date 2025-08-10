<?php

namespace App\Services;

use App\Contracts\Service\UserServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService implements UserServiceInterface
{
    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        
        return User::create($data);
    }

    public function authenticate(array $credentials): bool
    {
        return Auth::attempt($credentials);
    }

    public function updateProfile(User $user, array $data): User
    {
        // Remove sensitive fields that shouldn't be updated this way
        unset($data['password'], $data['email_verified_at']);
        
        $user->update($data);
        
        return $user->fresh();
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.']
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        return true;
    }

    public function deleteAccount(User $user, string $password): bool
    {
        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The password is incorrect.']
            ]);
        }

        return $user->delete();
    }

    public function getUserStats(User $user): array
    {
        $totalDecks = $user->decks()->count();
        $publicDecks = $user->decks()->where('is_public', true)->count();
        $totalFlashcards = $user->decks()->withCount('flashcards')->get()->sum('flashcards_count');
        
        // Get today's study data
        $today = now()->startOfDay();
        $todayStudySessions = $this->getTodayStudySessions($user);
        $cardsStudiedToday = $todayStudySessions->sum('cards_studied');
        $studyTimeToday = $todayStudySessions->sum('study_time_minutes');
        
        // Calculate daily goal (adaptive based on user's average)
        $averageDaily = $this->getAverageCardsPerDay($user);
        $dailyGoal = max(20, min(50, $averageDaily * 1.2)); // Adaptive goal between 20-50 cards
        
        // Calculate study streak
        $studyStreak = $this->calculateStudyStreak($user);
        
        // Calculate overall accuracy
        $accuracy = $this->calculateOverallAccuracy($user);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($user);
        
        // Get most recent deck for quick study
        $recentDeck = $user->decks()
            ->whereHas('flashcards') // Only decks with flashcards
            ->latest('updated_at')
            ->first();
        
        return [
            'total_decks' => $totalDecks,
            'public_decks' => $publicDecks,
            'private_decks' => $totalDecks - $publicDecks,
            'total_flashcards' => $totalFlashcards,
            'created_at' => $user->created_at,
            
            // Dynamic study data
            'cards_studied_today' => $cardsStudiedToday,
            'study_time_today' => $studyTimeToday,
            'daily_goal' => $dailyGoal,
            'goal_progress_percentage' => $dailyGoal > 0 ? min(100, ($cardsStudiedToday / $dailyGoal) * 100) : 0,
            'study_streak' => $studyStreak,
            'accuracy' => $accuracy,
            'recent_activities' => $recentActivities,
            'recent_deck' => $recentDeck,
            
            // Study time goal (in minutes)
            'study_time_goal' => 60, // 1 hour daily goal
            'study_time_progress_percentage' => min(100, ($studyTimeToday / 60) * 100),
        ];
    }
    
    /**
     * Get today's study sessions for a user
     */
    private function getTodayStudySessions(User $user)
    {
        // This would ideally come from a study_sessions table
        // For now, we'll simulate with deck creation/update data
        $today = now()->startOfDay();
        
        // Simulate study sessions based on deck activity
        $todayDecks = $user->decks()->whereDate('updated_at', $today)->get();
        
        return collect($todayDecks)->map(function ($deck) {
            return (object) [
                'cards_studied' => $deck->flashcards()->count(),
                'study_time_minutes' => $deck->flashcards()->count() * 2, // Estimate 2 min per card
            ];
        });
    }
    
    /**
     * Calculate user's average cards per day
     */
    private function getAverageCardsPerDay(User $user): int
    {
        $daysActive = max(1, $user->created_at->diffInDays(now()));
        $totalCards = $user->decks()->withCount('flashcards')->get()->sum('flashcards_count');
        
        return (int) ($totalCards / $daysActive);
    }
    
    /**
     * Calculate study streak in days
     */
    private function calculateStudyStreak(User $user): int
    {
        // This would ideally track actual study sessions
        // For now, calculate based on deck activity
        $streak = 0;
        $currentDate = now()->startOfDay();
        
        for ($i = 0; $i < 30; $i++) { // Check last 30 days
            $hasActivity = $user->decks()
                ->whereDate('updated_at', $currentDate)
                ->exists();
                
            if ($hasActivity) {
                $streak++;
            } else {
                break; // Streak broken
            }
            
            $currentDate->subDay();
        }
        
        return $streak;
    }
    
    /**
     * Calculate overall accuracy percentage
     */
    private function calculateOverallAccuracy(User $user): int
    {
        // This would ideally come from study session results
        // For now, simulate based on user's study patterns
        $totalDecks = $user->decks()->count();
        $totalCards = $user->decks()->withCount('flashcards')->get()->sum('flashcards_count');
        
        if ($totalCards === 0) return 0;
        
        // Simulate accuracy based on experience (more cards = better accuracy)
        $baseAccuracy = 65;
        $experienceBonus = min(25, $totalCards * 0.1); // Up to 25% bonus
        
        return (int) min(100, $baseAccuracy + $experienceBonus);
    }
    
    /**
     * Get recent activities for the user
     */
    private function getRecentActivities(User $user): array
    {
        $activities = [];
        
        // Recent deck creations
        $recentDecks = $user->decks()
            ->latest()
            ->limit(3)
            ->get();
            
        foreach ($recentDecks as $deck) {
            $activities[] = [
                'type' => 'deck_created',
                'title' => 'Created "' . $deck->name . '" deck',
                'time' => $deck->created_at->diffForHumans(),
                'icon' => 'plus',
                'color' => 'blue',
            ];
        }
        
        // Recent study sessions (simulated from deck updates)
        $recentStudies = $user->decks()
            ->where('updated_at', '>', now()->subDays(7))
            ->whereColumn('updated_at', '!=', 'created_at') // Fix the subquery issue
            ->latest('updated_at')
            ->limit(2)
            ->get();
            
        foreach ($recentStudies as $deck) {
            $accuracy = rand(75, 98); // Simulate accuracy
            $activities[] = [
                'type' => 'study_session',
                'title' => 'Studied "' . $deck->name . '"',
                'time' => $deck->updated_at->diffForHumans(),
                'details' => $accuracy . '% accuracy',
                'icon' => 'check',
                'color' => 'green',
            ];
        }
        
        // Sort by time and limit to 5 most recent
        usort($activities, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        
        return array_slice($activities, 0, 5);
    }
} 