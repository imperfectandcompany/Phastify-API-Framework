<?php
$dashboardMetrics = new DashboardMetrics($this->prodDbConnection);

// Consolidated functions for service metrics
$serviceMetrics = [
    'Total Services' => $dashboardMetrics->getTotalServices(),
    'All Services' => count($dashboardMetrics->getAllServices()["results"]),
    // Convert the array to count
    'Active Services' => count($dashboardMetrics->getActiveServices()["results"]),
    'Inactive Services' => count($dashboardMetrics->getInactiveServices()["results"]),
    'Service Popularity' => $dashboardMetrics->getServicePopularity(),
    'Most Popular Services' => (count($dashboardMetrics->getMostPopularServices(5)) > 0) ? $dashboardMetrics->getMostPopularServices(5) : 'No popular services found',
    'Active Services Count' => $dashboardMetrics->getActiveServicesCount(),
    'Total Integrations Count' => $dashboardMetrics->getTotalIntegrationsCount(),
    'Metrics for All Services' => $dashboardMetrics->calculateMetricsForAllServices()
];
?>