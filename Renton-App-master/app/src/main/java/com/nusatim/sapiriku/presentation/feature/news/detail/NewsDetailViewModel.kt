package com.nusatim.sapiriku.presentation.feature.news.detail
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.domain.model.NewsDetail
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.usecase.GetNewsDetailUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class NewsDetailViewModel @Inject constructor(
    private val getNewsDetailUseCase: GetNewsDetailUseCase
) : ViewModel() {

    private val _detail = MutableStateFlow<UiState<NewsDetail>>(UiState.Idle)
    val detail = _detail.asStateFlow()

    fun loadDetail(id: Int) {
        viewModelScope.launch {
            getNewsDetailUseCase(id).collect { resource ->
                _detail.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
