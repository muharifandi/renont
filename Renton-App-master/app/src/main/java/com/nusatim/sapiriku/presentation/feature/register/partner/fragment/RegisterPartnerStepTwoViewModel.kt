package com.nusatim.sapiriku.presentation.feature.register.partner.fragment
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.usecase.CheckAgentUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject
import com.nusatim.sapiriku.presentation.feature.register.customer.fragment.FieldCheckState

@HiltViewModel
class RegisterPartnerStepTwoViewModel @Inject constructor(
    private val checkAgentUseCase: CheckAgentUseCase
) : ViewModel() {

    private val _agentState = MutableStateFlow<FieldCheckState>(FieldCheckState.Idle)
    val agentState = _agentState.asStateFlow()

    private var agentJob: Job? = null

    fun checkAgent(agentId: String) {
        agentJob?.cancel()
        agentJob = viewModelScope.launch {
            checkAgentUseCase(agentId).collect { resource ->
                _agentState.value = when (resource) {
                    is Resource.Loading -> FieldCheckState.Checking
                    is Resource.Success -> if (resource.data.isValid) {
                        FieldCheckState.Valid(resource.data.message)
                    } else {
                        FieldCheckState.Invalid(resource.data.message)
                    }
                    is Resource.Error -> FieldCheckState.Invalid(resource.message)
                    is Resource.Empty -> FieldCheckState.Idle
                }
            }
        }
    }

    fun resetAgent() {
        agentJob?.cancel()
        _agentState.value = FieldCheckState.Idle
    }
}
