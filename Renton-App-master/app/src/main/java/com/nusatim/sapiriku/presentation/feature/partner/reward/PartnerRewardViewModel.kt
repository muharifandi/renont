package com.nusatim.sapiriku.presentation.feature.partner.reward
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.BasicData
import com.nusatim.sapiriku.domain.model.PartnerReward
import com.nusatim.sapiriku.domain.usecase.ClaimPartnerRewardUseCase
import com.nusatim.sapiriku.domain.usecase.GetPartnerRewardDetailUseCase
import com.nusatim.sapiriku.domain.usecase.ListPartnerRewardScopesUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class PartnerRewardViewModel @Inject constructor(
    private val listPartnerRewardScopesUseCase: ListPartnerRewardScopesUseCase,
    private val getPartnerRewardDetailUseCase: GetPartnerRewardDetailUseCase,
    private val claimPartnerRewardUseCase: ClaimPartnerRewardUseCase
) : ViewModel() {

    private val _scopes = MutableStateFlow<UiState<List<BasicData>>>(UiState.Idle)
    val scopes = _scopes.asStateFlow()

    private val _rewards = MutableStateFlow<UiState<List<PartnerReward>>>(UiState.Idle)
    val rewards = _rewards.asStateFlow()

    fun loadScopes() {
        viewModelScope.launch {
            listPartnerRewardScopesUseCase().collect { resource ->
                _scopes.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun loadRewards(scope: Int) {
        viewModelScope.launch {
            getPartnerRewardDetailUseCase(scope).collect { resource ->
                _rewards.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun claimReward(rewardId: Int, scope: Int) {
        viewModelScope.launch {
            claimPartnerRewardUseCase(rewardId).collect { resource ->
                if (resource is Resource.Success) {
                    loadRewards(scope)
                }
            }
        }
    }
}
