<?php

declare(strict_types=1);

namespace Breeze\Traits;

/**
 * Trait for collecting pagination performance metrics
 * 
 * This trait provides methods to log and track pagination performance
 * for monitoring and optimization purposes.
 */
trait PaginationMetricsTrait
{
	/**
	 * Log pagination metrics
	 * 
	 * @param string $method The pagination method used ('offset' or 'cursor')
	 * @param int $wallId The wall ID being paginated
	 * @param int $start The start offset (for offset-based) or 0 (for cursor-based)
	 * @param string|null $cursor The cursor value (if cursor-based)
	 * @param int $resultCount Number of results returned
	 * @param float $executionTime Query execution time in seconds
	 */
	protected function logPaginationMetrics(
		string $method,
		int $wallId,
		int $start,
		?string $cursor,
		int $resultCount,
		float $executionTime
	): void {
		// Only log if debug mode is enabled
		if (!$this->isDebugMode()) {
			return;
		}

		$metrics = [
			'timestamp' => time(),
			'method' => $method,
			'wall_id' => $wallId,
			'start' => $start,
			'cursor' => $cursor !== null ? substr($cursor, 0, 20) . '...' : null,
			'result_count' => $resultCount,
			'execution_time_ms' => round($executionTime * 1000, 2),
		];

		// Log to SMF's error log if available
		if (function_exists('log_error')) {
			log_error(
				sprintf(
					'[Breeze Pagination] Method: %s, Wall: %d, Start: %d, Results: %d, Time: %.2fms',
					$method,
					$wallId,
					$start,
					$resultCount,
					$metrics['execution_time_ms']
				),
				'debug'
			);
		}

		// Store metrics in cache for analysis
		$this->storePaginationMetrics($metrics);
	}

	/**
	 * Store pagination metrics in cache
	 * 
	 * @param array $metrics The metrics to store
	 */
	protected function storePaginationMetrics(array $metrics): void
	{
		$cacheKey = 'breeze_pagination_metrics';
		$ttl = 3600; // 1 hour

		// Get existing metrics
		$existingMetrics = cache_get_data($cacheKey, $ttl);
		if (!is_array($existingMetrics)) {
			$existingMetrics = [];
		}

		// Add new metrics (keep last 100 entries)
		$existingMetrics[] = $metrics;
		if (count($existingMetrics) > 100) {
			$existingMetrics = array_slice($existingMetrics, -100);
		}

		// Store back to cache
		cache_put_data($cacheKey, $existingMetrics, $ttl);
	}

	/**
	 * Get pagination metrics summary
	 * 
	 * @return array Summary of pagination metrics
	 */
	public function getPaginationMetricsSummary(): array
	{
		$cacheKey = 'breeze_pagination_metrics';
		$metrics = cache_get_data($cacheKey, 3600);

		if (!is_array($metrics) || empty($metrics)) {
			return [
				'total_queries' => 0,
				'cursor_queries' => 0,
				'offset_queries' => 0,
				'avg_cursor_time_ms' => 0,
				'avg_offset_time_ms' => 0,
			];
		}

		$cursorMetrics = array_filter($metrics, fn ($m) => $m['method'] === 'cursor');
		$offsetMetrics = array_filter($metrics, fn ($m) => $m['method'] === 'offset');

		$avgCursorTime = !empty($cursorMetrics)
			? array_sum(array_column($cursorMetrics, 'execution_time_ms')) / count($cursorMetrics)
			: 0;

		$avgOffsetTime = !empty($offsetMetrics)
			? array_sum(array_column($offsetMetrics, 'execution_time_ms')) / count($offsetMetrics)
			: 0;

		return [
			'total_queries' => count($metrics),
			'cursor_queries' => count($cursorMetrics),
			'offset_queries' => count($offsetMetrics),
			'avg_cursor_time_ms' => round($avgCursorTime, 2),
			'avg_offset_time_ms' => round($avgOffsetTime, 2),
			'improvement_percent' => $avgOffsetTime > 0
				? round((($avgOffsetTime - $avgCursorTime) / $avgOffsetTime) * 100, 1)
				: 0,
		];
	}

	/**
	 * Check if debug mode is enabled
	 * 
	 * @return bool True if debug mode is enabled
	 */
	protected function isDebugMode(): bool
	{
		// Check SMF's debug mode or custom Breeze debug setting
		return !empty($GLOBALS['db_show_debug']) || !empty($GLOBALS['breeze_debug']);
	}

	/**
	 * Clear pagination metrics
	 */
	public function clearPaginationMetrics(): void
	{
		cache_put_data('breeze_pagination_metrics', null, 0);
	}
}
