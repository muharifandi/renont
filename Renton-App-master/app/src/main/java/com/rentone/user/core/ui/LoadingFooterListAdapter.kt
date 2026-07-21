package com.rentone.user.core.ui

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.rentone.user.databinding.ItemLoadingBinding

/**
 * [ListAdapter] with an optional trailing loading-spinner row, replacing the legacy
 * addLoading()/removeLoading()/notifyDataSetChanged() pattern duplicated across list adapters.
 * Callers submit the full item list via [submitList] and toggle the footer via [setLoading].
 */
abstract class LoadingFooterListAdapter<T : Any>(
    diffCallback: DiffUtil.ItemCallback<T>
) : ListAdapter<T, RecyclerView.ViewHolder>(diffCallback) {

    private var showLoading = false

    fun setLoading(loading: Boolean) {
        if (showLoading == loading) return
        if (loading) {
            showLoading = true
            notifyItemInserted(itemCount - 1)
        } else {
            val removedIndex = itemCount - 1
            showLoading = false
            notifyItemRemoved(removedIndex)
        }
    }

    override fun getItemCount(): Int = super.getItemCount() + if (showLoading) 1 else 0

    override fun getItemViewType(position: Int): Int =
        if (showLoading && position == itemCount - 1) VIEW_TYPE_LOADING else VIEW_TYPE_ITEM

    final override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): RecyclerView.ViewHolder {
        return if (viewType == VIEW_TYPE_LOADING) {
            LoadingViewHolder(ItemLoadingBinding.inflate(LayoutInflater.from(parent.context), parent, false))
        } else {
            onCreateItemViewHolder(parent)
        }
    }

    final override fun onBindViewHolder(holder: RecyclerView.ViewHolder, position: Int) {
        if (holder is LoadingViewHolder) return
        onBindItemViewHolder(holder, getItem(position), position)
    }

    protected abstract fun onCreateItemViewHolder(parent: ViewGroup): RecyclerView.ViewHolder
    protected abstract fun onBindItemViewHolder(holder: RecyclerView.ViewHolder, item: T, position: Int)

    private class LoadingViewHolder(binding: ItemLoadingBinding) : RecyclerView.ViewHolder(binding.root)

    private companion object {
        const val VIEW_TYPE_LOADING = 0
        const val VIEW_TYPE_ITEM = 1
    }
}
