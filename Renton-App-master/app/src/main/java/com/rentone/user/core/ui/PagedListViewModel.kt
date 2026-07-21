package com.rentone.user.core.ui

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import kotlinx.coroutines.Job
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

data class PagedListUiState<T>(
    val items: List<T> = emptyList(),
    val isInitialLoading: Boolean = false,
    val isLoadingMore: Boolean = false,
    val isRefreshing: Boolean = false,
    val isLastPage: Boolean = false,
    val error: String? = null
)

/**
 * Base ViewModel for the page/limit + RecyclerView + SwipeRefreshLayout + loading-footer
 * pattern duplicated across the app's paginated list screens.
 */
abstract class PagedListViewModel<T>(private val pageSize: Int = 10) : ViewModel() {

    private val _uiState = MutableStateFlow(PagedListUiState<T>())
    val uiState = _uiState.asStateFlow()

    private var currentPage = 1
    private var loadJob: Job? = null

    protected abstract suspend fun fetchPage(page: Int, pageSize: Int): Result<List<T>>

    fun loadFirstPage(isRefresh: Boolean = false) {
        loadJob?.cancel()
        currentPage = 1
        loadJob = viewModelScope.launch {
            _uiState.value = _uiState.value.copy(
                isInitialLoading = !isRefresh,
                isRefreshing = isRefresh,
                error = null
            )
            val result = fetchPage(currentPage, pageSize)
            _uiState.value = result.fold(
                onSuccess = { items ->
                    _uiState.value.copy(
                        items = items,
                        isInitialLoading = false,
                        isRefreshing = false,
                        isLastPage = items.size < pageSize,
                        error = null
                    )
                },
                onFailure = { e ->
                    _uiState.value.copy(isInitialLoading = false, isRefreshing = false, error = e.message)
                }
            )
        }
    }

    fun loadMore() {
        val state = _uiState.value
        if (state.isLoadingMore || state.isLastPage || state.isInitialLoading) return

        loadJob = viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoadingMore = true)
            val nextPage = currentPage + 1
            val result = fetchPage(nextPage, pageSize)
            _uiState.value = result.fold(
                onSuccess = { items ->
                    if (items.isEmpty()) {
                        _uiState.value.copy(isLoadingMore = false, isLastPage = true)
                    } else {
                        currentPage = nextPage
                        _uiState.value.copy(
                            items = _uiState.value.items + items,
                            isLoadingMore = false,
                            isLastPage = items.size < pageSize
                        )
                    }
                },
                onFailure = {
                    _uiState.value.copy(isLoadingMore = false)
                }
            )
        }
    }

    val isLastPage: Boolean get() = _uiState.value.isLastPage
    val isLoadingMore: Boolean get() = _uiState.value.isLoadingMore
}
