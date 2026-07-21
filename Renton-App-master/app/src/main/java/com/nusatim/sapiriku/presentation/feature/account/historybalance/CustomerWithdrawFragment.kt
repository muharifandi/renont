package com.nusatim.sapiriku.presentation.feature.account.historybalance
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.core.view.isVisible
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.LinearLayoutManager
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.custom.PaginationListener
import com.nusatim.sapiriku.databinding.FragmentCustomerWithdrawBinding
import com.nusatim.sapiriku.presentation.feature.account.historybalance.adapter.ListWithdrawAdapter
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch

@AndroidEntryPoint
class CustomerWithdrawFragment : Fragment() {

    private var _binding: FragmentCustomerWithdrawBinding? = null
    private val binding get() = _binding!!

    private val viewModel: CustomerWithdrawViewModel by viewModels()
    private val adapter = ListWithdrawAdapter()

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentCustomerWithdrawBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

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
