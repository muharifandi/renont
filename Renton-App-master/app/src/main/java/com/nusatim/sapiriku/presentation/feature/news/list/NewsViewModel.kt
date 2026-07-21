package com.nusatim.sapiriku.presentation.feature.news.list
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.News
import com.nusatim.sapiriku.domain.usecase.GetNewsListUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class NewsViewModel @Inject constructor(
    private val getNewsListUseCase: GetNewsListUseCase
) : ViewModel() {

    private val _newsList = MutableStateFlow<UiState<List<News>>>(UiState.Idle)
    val newsList = _newsList.asStateFlow()

    private var currentPage = 1
    private val limit = 10
    private var isLastPage = false

    fun fetchNews(isRefresh: Boolean = false) {
        if (isRefresh) {
            currentPage = 1
            isLastPage = false
        }
        
        if (isLastPage) return

        viewModelScope.launch {
            val params = mapOf(
                "page" to currentPage.toString(),
                "limit" to limit.toString()
            )
            getNewsListUseCase(params).collect { resource ->
                when (resource) {
                    is Resource.Loading -> {
                        if (currentPage == 1) _newsList.value = UiState.Loading
                    }
                    is Resource.Success -> {
                        val currentData = if (currentPage == 1) emptyList() else {
                            (_newsList.value as? UiState.Success)?.data ?: emptyList()
                        }
                        val newData = resource.data
                        if (newData.isEmpty()) {
                            isLastPage = true
                        } else {
                            _newsList.value = UiState.Success(currentData + newData)
                            currentPage++
                        }
                    }
                    is Resource.Error -> {
                        _newsList.value = UiState.Error(resource.message)
                    }
                    else -> Unit
                }
            }
        }
    }
}
