package com.rentone.user.presentation.feature.account.historybalance
import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.view.isVisible
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import android.widget.Toast
import com.rentone.user.R
import com.rentone.user.custom.PaginationListener
import com.rentone.user.databinding.FragmentCustomerTopupBinding
import com.rentone.user.presentation.feature.account.historybalance.adapter.ListTopupAdapter
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition
import com.rentone.user.presentation.feature.account.verificationtopup.CustomerVerificationTopupActivity

@AndroidEntryPoint
class CustomerTopupFragment : Fragment() {

    private var _binding: FragmentCustomerTopupBinding? = null
    private val binding get() = _binding!!

    private val viewModel: CustomerTopupViewModel by viewModels()
    private lateinit var adapter: ListTopupAdapter

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentCustomerTopupBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        adapter = ListTopupAdapter(
            onItemClick = { topup ->
                val intent = Intent(requireContext(), CustomerVerificationTopupActivity::class.java)
                intent.putExtra("topup_id", topup.id)
                startActivity(intent)
                requireActivity().applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
            }
        )

        val layoutManager = LinearLayoutManager(requireContext())
        binding.list.layoutManager = layoutManager
        binding.list.adapter = adapter
        binding.list.addOnScrollListener(object : PaginationListener(layoutManager) {
            override fun loadMoreItems() = viewModel.loadMore()
            override fun isLastPage() = viewModel.isLastPage
            override fun isLoading() = viewModel.isLoadingMore
        })

        binding.srLayout.setOnRefreshListener { viewModel.loadFirstPage(isRefresh = true) }

        observeState()
        viewModel.loadFirstPage()
    }

    private fun observeState() {
        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.uiState.collect { state ->
                    binding.srLayout.isRefreshing = state.isRefreshing
                    adapter.setLoading(state.isLoadingMore)
                    adapter.submitList(state.items)

                    val isEmpty = state.items.isEmpty() && !state.isInitialLoading
                    binding.list.isVisible = !isEmpty
                    binding.txtListMessage.isVisible = isEmpty

                    if (state.error != null) {
                        Toast.makeText(requireContext(), getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                    }
                }
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
