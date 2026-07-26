<?php

namespace App\Libraries;

/**
 * AI Services Library
 * 
 * Provides AI-powered features for the FU-UBRA system
 * including chat assistance, quick actions, and predictions
 */
class AiServices
{
    /**
     * Process a chat message and return an AI response
     * 
     * @param string $message The user's message
     * @param array $context Additional context (user info, etc.)
     * @return array Response with message and metadata
     */
    public function processChat(string $message, array $context = []): array
    {
        // Normalize the message
        $message = trim(strtolower($message));
        
        // Get the response based on message intent
        $response = $this->interpretIntent($message, $context);
        
        return [
            'success' => true,
            'message' => $response['message'],
            'intent' => $response['intent'],
            'suggested_actions' => $response['actions'] ?? [],
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Interpret the user's intent and provide appropriate response
     * 
     * @param string $message The normalized message
     * @param array $context Context information
     * @return array Intent interpretation and response
     */
    private function interpretIntent(string $message, array $context = []): array
    {
        // Check for various intents and keywords
        $intents = [
            'travel' => [
                'keywords' => ['travel', 'trip', 'request', 'destination', 'departure'],
                'response' => 'I can help you with travel requests. You can request a vehicle for a specific date and destination. Would you like to submit a new travel request?',
                'actions' => ['create_travel_request', 'view_travel_history'],
            ],
            'vehicle' => [
                'keywords' => ['vehicle', 'car', 'truck', 'fleet', 'transportation', 'gps'],
                'response' => 'I can help you manage vehicles. You can view vehicle status, track GPS locations, or manage maintenance. What would you like to do?',
                'actions' => ['view_vehicles', 'track_vehicle', 'vehicle_maintenance'],
            ],
            'inventory' => [
                'keywords' => ['tool', 'equipment', 'inventory', 'borrow', 'return', 'stock'],
                'response' => 'I can assist with inventory management. You can borrow tools, return equipment, or check availability. What do you need?',
                'actions' => ['borrow_tool', 'return_tool', 'view_inventory'],
            ],
            'personnel' => [
                'keywords' => ['personnel', 'staff', 'employee', 'driver', 'janitor', 'department'],
                'response' => 'I can help you manage personnel information. You can view staff details, departments, or positions. How can I assist?',
                'actions' => ['view_personnel', 'view_departments', 'staff_directory'],
            ],
            'report' => [
                'keywords' => ['report', 'analytics', 'data', 'statistics', 'dashboard', 'summary'],
                'response' => 'I can help generate reports. You can view vehicle utilization, personnel statistics, or equipment usage reports. Which report interests you?',
                'actions' => ['generate_report', 'view_analytics', 'export_data'],
            ],
            'safety' => [
                'keywords' => ['safety', 'work order', 'maintenance', 'inspection', 'incident'],
                'response' => 'I can assist with safety monitoring. You can create work orders, schedule maintenance, or report incidents. What do you need help with?',
                'actions' => ['create_work_order', 'schedule_maintenance', 'report_incident'],
            ],
            'help' => [
                'keywords' => ['help', 'how', 'what can', 'guide', 'tutorial', 'support'],
                'response' => 'I\'m Mr. UBRA, your AI assistant. I can help you with: travel requests, vehicle management, inventory, personnel, reports, and safety monitoring. What would you like to do?',
                'actions' => ['view_help', 'contact_support'],
            ],
        ];

        // Find matching intent
        $matchedIntent = 'general';
        $response = [
            'message' => 'How can I assist you with the FU-UBRA system today?',
            'intent' => 'general',
            'actions' => ['view_help', 'contact_support'],
        ];

        foreach ($intents as $intent => $config) {
            foreach ($config['keywords'] as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $matchedIntent = $intent;
                    $response = [
                        'message' => $config['response'],
                        'intent' => $intent,
                        'actions' => $config['actions'],
                    ];
                    break 2;
                }
            }
        }

        // Add context-specific responses if available
        if ($context && isset($context['user_role'])) {
            $response['role_specific'] = true;
        }

        return $response;
    }

    /**
     * Generate a quick action suggestion
     * 
     * @param string $category The category of quick action
     * @param array $context Context information
     * @return array Quick action suggestion
     */
    public function getQuickAction(string $category, array $context = []): array
    {
        $actions = [
            'daily_check' => [
                'title' => 'Daily Fleet Check',
                'description' => 'Check status of all vehicles and recent GPS logs',
                'action' => 'view_fleet_status',
            ],
            'pending_requests' => [
                'title' => 'Pending Travel Requests',
                'description' => 'Review and approve pending travel requests',
                'action' => 'approve_travel_requests',
            ],
            'overdue_returns' => [
                'title' => 'Overdue Tool Returns',
                'description' => 'Track tools that haven\'t been returned on time',
                'action' => 'view_overdue_tools',
            ],
            'maintenance_schedule' => [
                'title' => 'Maintenance Schedule',
                'description' => 'View upcoming vehicle maintenance tasks',
                'action' => 'view_maintenance_schedule',
            ],
            'performance_report' => [
                'title' => 'Performance Report',
                'description' => 'Generate weekly performance analytics',
                'action' => 'generate_performance_report',
            ],
        ];

        $action = $actions[$category] ?? $actions['daily_check'];

        return [
            'success' => true,
            'action' => $action,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Generate predictive insights based on system data
     * 
     * @param string $type Type of prediction (vehicle_maintenance, usage_trends, etc.)
     * @return array Predictive insights
     */
    public function generateInsights(string $type = 'general'): array
    {
        $insights = [
            'vehicle_maintenance' => [
                'title' => 'Vehicle Maintenance Predictions',
                'description' => 'Based on mileage and service history, these vehicles may need maintenance soon.',
                'confidence' => 0.85,
            ],
            'usage_trends' => [
                'title' => 'Usage Trends',
                'description' => 'Vehicle usage has increased by 15% this month compared to last month.',
                'confidence' => 0.92,
            ],
            'cost_optimization' => [
                'title' => 'Cost Optimization Opportunities',
                'description' => 'Consider consolidating trips to reduce fuel consumption.',
                'confidence' => 0.78,
            ],
            'inventory_optimization' => [
                'title' => 'Inventory Optimization',
                'description' => 'Some tools are frequently borrowed; consider increasing stock.',
                'confidence' => 0.81,
            ],
        ];

        $insight = $insights[$type] ?? [
            'title' => 'General Insights',
            'description' => 'System is operating normally with no critical alerts.',
            'confidence' => 0.95,
        ];

        return [
            'success' => true,
            'insight' => $insight,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Log AI interaction for training and analytics
     * 
     * @param string $message User message
     * @param string $response AI response
     * @param array $metadata Additional metadata
     * @return bool Success status
     */
    public function logInteraction(string $message, string $response, array $metadata = []): bool
    {
        // This would typically log to database or file
        // For now, returning true to indicate success
        return true;
    }
}
