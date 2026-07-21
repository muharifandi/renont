package com.nusatim.sapiriku.presentation.feature.register.partner.fragment
import android.net.Uri
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.activity.result.contract.ActivityResultContracts
import androidx.core.view.isVisible
import androidx.core.widget.doAfterTextChanged
import androidx.fragment.app.viewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import coil.load
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.databinding.FragmentRegisterPartnerStepTwoBinding
import com.nusatim.sapiriku.presentation.feature.register.fragment.RegisterStepBaseFragment
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.nusatim.sapiriku.presentation.feature.register.customer.fragment.FieldCheckState

@AndroidEntryPoint
class RegisterPartnerStepTwoFragment : RegisterStepBaseFragment() {

    private var _binding: FragmentRegisterPartnerStepTwoBinding? = null
    private val binding get() = _binding!!

    private val viewModel: RegisterPartnerStepTwoViewModel by viewModels()

    private var identityPhotoUri: Uri? = null
    private var businessLicencePhotoUri: Uri? = null
    private var businessRegistrationPhotoUri: Uri? = null

    private var agentValid = false

    private val pickIdentityPhoto = registerForActivityResult(ActivityResultContracts.GetContent()) { uri: Uri? ->
        uri?.let {
            identityPhotoUri = it
            binding.imageIdentity.load(it) { error(R.drawable.ic_insert_drive_file_black_24dp) }
        }
    }

    private val pickBusinessLicencePhoto = registerForActivityResult(ActivityResultContracts.GetContent()) { uri: Uri? ->
        uri?.let {
            businessLicencePhotoUri = it
            binding.imageBussinessLicence.load(it) { error(R.drawable.ic_insert_drive_file_black_24dp) }
        }
    }

    private val pickBusinessRegistrationPhoto = registerForActivityResult(ActivityResultContracts.GetContent()) { uri: Uri? ->
        uri?.let {
            businessRegistrationPhotoUri = it
            binding.imageBussinessRegistration.load(it) { error(R.drawable.ic_insert_drive_file_black_24dp) }
        }
    }

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentRegisterPartnerStepTwoBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        // Registration is always for a company (the personal/ownership toggle was already
        // disabled in the legacy app), so the driver-licence document is never required.
        binding.containerDriverLicence.isVisible = false
        binding.containerBussinessLicence.isVisible = true
        binding.containerBussinessRegistration.isVisible = true

        binding.inputAgent.doAfterTextChanged { text ->
            binding.txtAgentMessage.text = ""
            val value = text.toString()
            if (value.isEmpty()) {
                viewModel.resetAgent()
                binding.inputAgentLayout.error = getString(R.string.agent_cannot_empty)
                binding.checkAgent.uncheck()
            } else {
                binding.inputAgentLayout.error = getString(R.string.checking_agent)
                binding.checkAgent.uncheck()
                viewModel.checkAgent(value)
            }
        }

        binding.imageIdentity.setOnClickListener { pickIdentityPhoto.launch("image/*") }
        binding.imageBussinessLicence.setOnClickListener { pickBusinessLicencePhoto.launch("image/*") }
        binding.imageBussinessRegistration.setOnClickListener { pickBusinessRegistrationPhoto.launch("image/*") }

        observeState()
    }

    private fun observeState() {
        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.agentState.collect { state ->
                    when (state) {
                        is FieldCheckState.Valid -> {
                            agentValid = true
                            binding.inputAgentLayout.error = null
                            binding.txtAgentMessage.text = state.message
                            binding.checkAgent.check()
                        }
                        is FieldCheckState.Invalid -> {
                            agentValid = false
                            binding.inputAgentLayout.error = state.message
                            binding.checkAgent.uncheck()
                        }
                        else -> Unit
                    }
                }
            }
        }
    }

    override fun validateForm(): Boolean {
        if (binding.inputAgent.text.toString().isEmpty()) {
            binding.inputAgentLayout.error = getString(R.string.agent_cannot_empty)
            binding.checkAgent.uncheck()
        }
        return agentValid && identityPhotoUri != null
    }

    override fun getFormValue(): Map<String, String> = mapOf(
        "tax_number" to binding.inputTaxNumber.text.toString(),
        "agent_id" to binding.inputAgent.text.toString(),
        "img_identity" to (identityPhotoUri?.toString() ?: ""),
        "img_driver_licence" to "",
        "img_bussiness_licence" to (businessLicencePhotoUri?.toString() ?: ""),
        "img_bussiness_registration" to (businessRegistrationPhotoUri?.toString() ?: "")
    )

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
